<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationsAssociatedToUserManager
{
    use UserAssociationManagerTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $criteria
     * @return UserAssociationQuery
     */
    public function query(array $criteria = []): UserAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = UserAssociation::find()
            ->setUserId($this->user->getId() ?: false)
            ->orderBy([
                'organizationOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * @param $object
     * @return UserAssociation
     */
    public function create($object): UserAssociation
    {
        return (new UserAssociation())
            ->setOrganization($this->resolveOrganization($object))
            ->setUser($this->user);
    }


    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function save(): bool
    {
        // No changes?
        if (!$this->isMutated()) {
            return true;
        }

        $success = true;

        $existingAssociations = $this->query()
            ->indexBy('organizationId')
            ->all();

        $associations = [];
        foreach ($this->findAll() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getOrganizationId()
            ))) {
                $association = $newAssociation;
            }

            $association->userOrder = $newAssociation->userOrder;
            $association->organizationOrder = $newAssociation->organizationOrder;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($existingAssociations as $existingAssociation) {
            if (!$existingAssociation->delete()) {
                $success = false;
            }
        }

        $order = 1;
        foreach ($associations as $association) {
            $association->organizationOrder = $order++;

            if (!$association->save()) {
                $success = false;
            }
        }

        $this->associations = $associations;

        if (!$success) {
            $this->user->addError('organizations', 'Unable to save user organizations.');
        }

        return $success;
    }

    /*******************************************
     * ASSOCIATE
     *******************************************/

    /**
     * @param $object
     * @param int|null $sortOrder
     * @return bool
     */
    public function associateOne($object, int $sortOrder = null): bool
    {
        $association = $this->findOrCreate($object);

        if (null !== $sortOrder) {
            $association->organizationOrder = $sortOrder;
        }

        if (!$association->save()) {
            $this->user->addError('organizations', 'Unable to associate organization.');

            return false;
        }

        $this->reset();

        return true;
    }

    /**
     * @param QueryInterface|Organization[] $objects
     * @return bool
     * @throws \Throwable
     */
    public function associateMany($objects): bool
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        if (empty($objects)) {
            return true;
        }

        $this->addMany($objects);

        return $this->save();
    }


    /*******************************************
     * DISSOCIATE
     *******************************************/

    /**
     * @param $object
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociateOne($object): bool
    {
        if (null === ($association = $this->findOne($object))) {
            return true;
        }

        if (!$association->delete()) {
            $this->user->addError('organizations', 'Unable to dissociate organization.');

            return false;
        }

        $this->removeOne($association);

        return true;
    }

    /**
     * @param QueryInterface|Organization[] $objects
     * @return bool
     * @throws \Throwable
     */
    public function dissociateMany($objects): bool
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        if (empty($objects)) {
            return true;
        }

        $this->removeMany($objects);

        return $this->save();
    }

    /**
     * @param UserAssociation|Organization|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if (null === ($element = $this->resolveOrganization($object))) {
            Organizations::info(sprintf(
                "Unable to resolve organization: %s",
                (string)Json::encode($object)
            ));
            return null;
        }

        foreach ($this->findAll() as $key => $association) {
            if ($association->getOrganizationId() === $element->getId()) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserAssociation|Organization|int|array|null $organization
     * @return Organization|null
     */
    protected function resolveOrganization($organization = null)
    {
        if (null === $organization) {
            return null;
        }

        if ($organization instanceof UserAssociation) {
            return $organization->getOrganization();
        }

        if ($organization instanceof Organization) {
            return $organization;
        }

        if (is_array($organization) &&
            null !== ($id = ArrayHelper::getValue($organization, 'id'))
        ) {
            $organization = ['id' => $id];
        }

        return Organization::findOne($organization);
    }
}
