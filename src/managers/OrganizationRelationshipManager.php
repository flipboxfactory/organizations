<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use Craft;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;

/**
 * Manages Organizations associated to Users
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.00
 *
 * @property UserAssociation[] $associations
 *
 * @method UserAssociation findOrCreate($object)
 * @method UserAssociation findOne($object = null)
 * @method UserAssociation findOrFail($object)
 */
class OrganizationRelationshipManager implements RelationshipManagerInterface
{
    use RelationshipManagerTrait;

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
    protected function query(array $criteria = []): UserAssociationQuery
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
    protected function create($object): UserAssociation
    {
        return (new UserAssociation())
            ->setOrganization($this->resolveOrganization($object))
            ->setUser($this->user);
    }

    /**
     * @inheritDoc
     *
     * @param bool $addToOrganization
     */
    public function addOne($object, array $attributes = [], bool $addToOrganization = false)
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
            $this->addToCache($association);
        }

        if (!empty($attributes)) {
            Craft::configure(
                $association,
                $attributes
            );
        }

        // Add user to organization as well?
        if ($addToOrganization && $association->getOrganization()->getId() !== null) {
            $association->getOrganization()->getUserManager()->addOne($this->user, [], false);
        }

        return $this;
    }

    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function associationDelta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('organizationId')
            ->all();

        $associations = [];
        $order = 1;
        foreach ($this->findAll() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getOrganizationId()
            ))) {
                $association = $newAssociation;
            }

            $association->userOrder = $newAssociation->userOrder;
            $association->organizationOrder = $order++;

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @inheritDoc
     */
    protected function handleAssociationError()
    {
        $this->user->addError('organizations', 'Unable to save user organizations.');
    }


    /*******************************************
     * UTILS
     *******************************************/

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
            if ($association->getOrganizationId() == $element->getId()) {
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
