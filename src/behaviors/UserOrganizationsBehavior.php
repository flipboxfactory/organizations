<?php

namespace flipbox\organizations\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\validators\OrganizationsValidator;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Class UserOrganizationsBehavior
 * @package flipbox\organizations\behaviors
 *
 * @property User $owner;
 */
class UserOrganizationsBehavior extends Behavior
{
    /**
     * @var OrganizationQuery|null
     */
    private $organizations;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Validate organizations
        Event::on(
            User::class,
            User::EVENT_AFTER_VALIDATE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterValidate($user);
            }
        );

        // Associate
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function (ModelEvent $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterSave($user);
            }
        );

        // Dissociate
        Event::on(
            User::class,
            User::EVENT_AFTER_DELETE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterDelete($user);
            }
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     */
    private function onAfterDelete(User $user)
    {
        /** @var UserOrganizationsBehavior $user */
        // Remove organizations
        $user->setOrganizations([]);

        // Save associations (which is really deleting them all)
        $user->saveOrganizations();
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function onAfterSave(User $user)
    {
        // Check cache for explicitly set (and possibly not saved) organizations
        if (null !== ($organizations = $user->getOrganizations()->getCachedResult())) {

            /** @var Organization $organization */
            foreach ($organizations as $organization) {
                if (!$organization->id) {
                    if (!Craft::$app->getElements()->saveElement($organization)) {
                        $user->addError(
                            'organizations',
                            Craft::t('organizations', 'Unable to save organization.')
                        );

                        throw new Exception('Unable to save organization.');
                    }
                }
            }
        }

        $this->saveOrganizations();
    }

    /**
     * @param User|self $user
     * @return void
     */
    private function onAfterValidate(User $user)
    {
        $error = null;

        if (!(new OrganizationsValidator())->validate($user->getOrganizations(), $error)) {
            $user->addError('organizations', $error);
        }
    }

    /**
     * @param array $criteria
     * @return OrganizationQuery
     */
    public function organizationQuery($criteria = []): OrganizationQuery
    {
        $query = Organization::find()
            ->user($this->owner)
            ->orderBy([
                'organizationOrder' => SORT_ASC,
                'title' => SORT_ASC
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
     * Get a query with associated organizations
     *
     * @param array $criteria
     * @return OrganizationQuery
     */
    public function getOrganizations($criteria = []): OrganizationQuery
    {
        if (null === $this->organizations) {
            $this->organizations = $this->organizationQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->organizations,
                $criteria
            );
        }

        return $this->organizations;
    }

    /**
     * Set an array or query of organizations to a user
     *
     * @param $organizations
     * @return $this
     */
    public function setOrganizations($organizations)
    {
        if ($organizations instanceof OrganizationQuery) {
            $this->organizations = $organizations;
            return $this;
        }

        // Reset the query
        $this->organizations = $this->organizationQuery();
        $this->organizations->setCachedResult([]);
        $this->addOrganizations($organizations);
        return $this;
    }

    /**
     * Add an array of organizations to a user.  Note: This does not save the organization associations.
     *
     * @param $organizations
     * @return $this
     */
    protected function addOrganizations(array $organizations)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($organizations)) {
            $organizations = [$organizations];
        }

        foreach ($organizations as $key => $organization) {
            if (!$organization = $this->resolveOrganization($organization)) {
                OrganizationPlugin::info(sprintf(
                    "Unable to resolve organization: %s",
                    (string)Json::encode($organization)
                ));
                continue;
            }

            $this->addOrganization($organization);
        }

        return $this;
    }

    /**
     * Add a organization to a user.  Note: This does not save the organization association.
     *
     * @param Organization $organization
     * @param bool $addToOrganization
     * @return $this
     */
    public function addOrganization(Organization $organization, bool $addToOrganization = true)
    {
        // Current associated organizations
        $allOrganizations = $this->getOrganizations()->all();
        $allOrganizations[] = $organization;

        $this->getOrganizations()->setCachedResult($allOrganizations);

        // Add user to organization as well?
        if ($addToOrganization && $organization->id !== null) {
            $user = $this->owner;
            if ($user instanceof User) {
                $organization->addUser($user);
            };
        }

        return $this;
    }

    /**
     * @param $organization
     * @return Organization
     */
    protected function resolveOrganization($organization)
    {
        if ($organization instanceof Organization) {
            return $organization;
        }

        if (is_array($organization) &&
            null !== ($id = ArrayHelper::getValue($organization, 'id'))
        ) {
            return Organization::findOne($id);
        }

        if (null !== ($object = Organization::findOne($organization))) {
            return $object;
        }

        return new Organization($organization);
    }

    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveOrganizations(): bool
    {
        // No changes?
        if (null === ($records = $this->getOrganizations()->getCachedResult())) {
            return true;
        }

        $currentAssociations = $this->currentAssociationQuery()->all();

        $success = true;
        $associations = [];
        $order = 1;
        foreach ($records as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($this->owner)
                    ->setOrganization($type);
            }

            $association->organizationOrder = $order++;

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
            $this->owner->addError('organizations', 'Unable to save user organizations.');
        }

        return $success;
    }

    /**
     * @param Organization $organization
     * @param int|null $sortOrder
     * @return bool
     */
    public function associateOrganization(Organization $organization, int $sortOrder = null): bool
    {
        if (null === ($association = UserAssociation::find()
                ->userId($this->owner->getId() ?: false)
                ->organizationId($organization->getId() ?: false)
                ->one())
        ) {
            $association = new UserAssociation([
                'organization' => $organization,
                'user' => $this->owner
            ]);
        }

        if (null !== $sortOrder) {
            $association->organizationOrder = $sortOrder;
        }

        if (!$association->save()) {
            $this->owner->addError('organizations', 'Unable to associate organization.');

            return false;
        }

        $this->resetOrganizations();

        return true;
    }

    /**
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Throwable
     */
    public function associateOrganizations(OrganizationQuery $query): bool
    {
        $organizations = $query->all();

        if (empty($organizations)) {
            return true;
        }

        $currentAssociations = $this->currentAssociationQuery()->all();

        $success = true;
        foreach ($organizations as $organization) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $organization->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($this->owner)
                    ->setOrganization($organization);
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->owner->addError('organizations', 'Unable to associate organizations.');
        }

        $this->resetOrganizations();

        return $success;
    }

    /**
     * @param Organization $organization
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociateOrganization(Organization $organization): bool
    {
        if (null === ($association = UserAssociation::find()
                ->userId($this->owner->getId() ?: false)
                ->organizationId($organization->getId() ?: false)
                ->one())
        ) {
            return true;
        }

        if (!$association->delete()) {
            $this->owner->addError('organizations', 'Unable to dissociate organization.');

            return false;
        }

        $this->resetOrganizations();

        return true;
    }

    /**
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Throwable
     */
    public function dissociateOrganizations(OrganizationQuery $query): bool
    {
        $organizations = $query->all();

        if (empty($organizations)) {
            return true;
        }

        $currentAssociations = $this->currentAssociationQuery()->all();

        $success = true;
        foreach ($organizations as $organization) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $organization->getId()))) {
                continue;
            }

            if (!$association->delete()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->owner->addError('organizations', 'Unable to associate organizations.');
        }

        $this->resetOrganizations();

        return $success;
    }

    /**
     * @return UserAssociationQuery
     */
    protected function currentAssociationQuery(): UserAssociationQuery
    {
        return UserAssociation::find()
            ->userId($this->owner->getId() ?: false)
            ->indexBy('organizationId')
            ->orderBy(['organizationOrder' => SORT_ASC]);
    }

    /**
     * @return User
     */
    public function resetOrganizations()
    {
        $this->organizations = null;
        return $this->owner;
    }
}
