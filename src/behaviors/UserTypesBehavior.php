<?php

namespace flipbox\organizations\behaviors;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\UserTypeQuery;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use yii\base\Behavior;
use yii\db\Query;

/**
 * @property User $owner;
 */
class UserTypesBehavior extends Behavior
{
    /**
     * @var UserTypeQuery|null
     */
    private $userTypes;

    /**
     * @param array $criteria
     * @return UserTypeQuery
     */
    public function userTypeQuery($criteria = []): UserTypeQuery
    {
        $query = UserType::find()
            ->user($this->owner);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * Get a query with associated user types
     *
     * @param array $criteria
     * @return UserTypeQuery
     */
    public function getUserTypes($criteria = []): UserTypeQuery
    {
        if (null === $this->userTypes) {
            $this->userTypes = $this->userTypeQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->userTypes,
                $criteria
            );
        }

        return $this->userTypes;
    }

    /**
     * Set an array or query of user types to a user
     *
     * @param $userTypes
     * @return $this
     */
    public function setUserTypes($userTypes)
    {
        if ($userTypes instanceof UserTypeQuery) {
            $this->userTypes = $userTypes;
            return $this;
        }

        // Reset the query
        $this->userTypes = $this->userTypeQuery();
        $this->userTypes->setCachedResult([]);
        $this->addUserTypes($userTypes);
        return $this;
    }

    /**
     * Add an array of user types to a user.  Note: This does not save the user type associations.
     *
     * @param $types
     * @return $this
     */
    protected function addUserTypes(array $types)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($types)) {
            $types = [$types];
        }

        foreach ($types as $key => $type) {
            if (!$type = $this->resolveUserType($type)) {
                OrganizationPlugin::warning(sprintf(
                    "Unable to resolve user type: %s",
                    (string)Json::encode($type)
                ));
                continue;
            }

            $this->addUserType($type);
        }

        return $this;
    }

    /**
     * Add a user type to a user.  Note: This does not save the user type associations.
     *
     * @param UserType $type
     * @return $this
     */
    public function addUserType(UserType $type)
    {
        // Current associated types
        $allTypes = $this->getUserTypes()->all();
        $allTypes[] = $type;

        $this->getUserTypes()->setCachedResult($allTypes);

        return $this;
    }

    /**
     * @param mixed $type
     * @return UserType
     */
    protected function resolveUserType($type): UserType
    {
        if (null !== ($type = UserType::findOne($type))) {
            return $type;
        }

        if (!is_array($type)) {
            $type = ArrayHelper::toArray($type, [], false);
        }

        return new UserType($type);
    }

    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param UserTypeQuery $query
     * @param Organization $organization
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveUserTypes(UserTypeQuery $query, Organization $organization): bool
    {
        // Determine an association
        if (null === ($userAssociationId = $this->associationId($this->owner->getId(), $organization->getId()))) {
            $this->owner->addError('types', 'User is not associated to organization.');
            return false;
        }

        $currentAssociations = UserTypeAssociation::find()
            ->userId($userAssociationId ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;

        if (null === ($types = $query->getCachedResult())) {
            // Delete anything that's currently set
            foreach ($currentAssociations as $currentAssociation) {
                if (!$currentAssociation->delete()) {
                    $success = false;
                }
            }

            if (!$success) {
                $this->owner->addError('types', 'Unable to dissociate types.');
            }

            return $success;
        }

        $associations = [];
        $order = 1;
        foreach ($types as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                $association = (new UserTypeAssociation())
                    ->setType($type);
                $association->userId = $userAssociationId;
            }

            $association->sortOrder = $order++;

            $associations[] = $association;
        }

        // Delete anything that has been removed
        foreach ($currentAssociations as $currentAssociation) {
            if (!$currentAssociation->delete()) {
                $success = false;
            }
        }

        // Save'em
        foreach ($associations as $association) {
            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->owner->addError('types', 'Unable to save user types.');
        }

        $this->setUserTypes($query);

        return $success;
    }

    /**
     * @param UserTypeQuery $query
     * @param Organization $organization
     * @return bool
     * @throws \Throwable
     */
    public function associateUserTypes(UserTypeQuery $query, Organization $organization): bool
    {
        $records = $query->all();

        if (empty($records)) {
            return true;
        }

        // Determine an association
        if (null === ($userAssociationId = $this->associationId($this->owner->getId(), $organization->getId()))) {
            $this->owner->addError('types', 'User is not associated to organization.');
            return false;
        }

        $currentAssociations = UserTypeAssociation::find()
            ->userId($userAssociationId ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;
        foreach ($records as $record) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $record->getId()))) {
                $association = (new UserTypeAssociation())
                    ->setType($record);
                $association->userId = $userAssociationId;
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->owner->addError('users', 'Unable to associate user types.');
        }

        $this->userTypes = null;

        return $success;
    }

    /**
     * @param UserTypeQuery $query
     * @param Organization $organization
     * @return bool
     * @throws \Throwable
     */
    public function dissociateUserTypes(UserTypeQuery $query, Organization $organization): bool
    {
        $records = $query->all();

        if (empty($records)) {
            return true;
        }

        // Determine an association
        if (null === ($userAssociationId = $this->associationId($this->owner->getId(), $organization->getId()))) {
            $this->owner->addError('types', 'User is not associated to organization.');
            return false;
        }

        $currentAssociations = UserTypeAssociation::find()
            ->userId($userAssociationId ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;
        foreach ($records as $record) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $record->getId()))) {
                continue;
            }

            if (!$association->delete()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->owner->addError('users', 'Unable to dissociate user types.');
        }

        $this->userTypes = null;

        return $success;
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return Query
     */
    protected function associationIdQuery(int $userId, int $organizationId): Query
    {
        return (new Query())
            ->select(['id'])
            ->from([UserAssociation::tableName()])
            ->where([
                'organizationId' => $organizationId,
                'userId' => $userId,
            ]);
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return string|null
     */
    protected function associationId(int $userId, int $organizationId)
    {
        $id = $this->associationIdQuery($userId, $organizationId)->scalar();
        return is_string($id) || is_numeric($id) ? $id : null;
    }
}
