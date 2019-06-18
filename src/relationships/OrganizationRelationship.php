<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use Tightenco\Collect\Support\Collection;

/**
 * Manages Organizations associated to Users
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 * *
 * @method UserAssociation findOrCreate($object)
 * @method UserAssociation findOne($object = null)
 * @method UserAssociation findOrFail($object)
 */
class OrganizationRelationship implements RelationshipInterface
{
    use RelationshipTrait;

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


    /************************************************************
     * COLLECTION
     ************************************************************/

    /**
     * @inheritDoc
     * @return Organization[]|Collection
     */
    public function getCollection(): Collection
    {
        if (null === $this->relations) {
            return new Collection(
                $this->elementQuery()
                    ->all()
            );
        }

        return $this->getRelationships()
            ->sortBy('organizationOrder')
            ->pluck('organization');
    }

    /**
     * @inheritDoc
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        $relationships = $this->associationQuery()
            ->with('types')
            ->all();

        // 'eager' load where we'll pre-populate all of the associations
        $elements = $this->elementQuery()
            ->id(array_keys($relationships))
            ->indexBy('id')
            ->all();

        return $this->createRelations($relationships)
            ->transform(function (UserAssociation $association) use ($elements) {
                if (isset($elements[$association->getOrganizationId()])) {
                    $association->setOrganization($elements[$association->getOrganizationId()]);
                }

                $association->setUser($this->user);

                return $association;
            });
    }

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return OrganizationQuery
     */
    protected function elementQuery(): OrganizationQuery
    {
        return Organization::find()
            ->userId($this->user->getId() ?: false)
            ->anyStatus()
            ->limit(null);
    }

    /**
     * @return UserAssociationQuery
     */
    protected function associationQuery(): UserAssociationQuery
    {
        return UserAssociation::find()
            ->setUserId($this->user->getId() ?: false)
            ->orderBy([
                'organizationOrder' => SORT_ASC
            ])
            ->limit(null);
    }


    /************************************************************
     * CREATE
     ************************************************************/

    /**
     * @param $object
     * @return UserAssociation
     */
    protected function create($object): UserAssociation
    {
        if ($object instanceof UserAssociation) {
            return $object;
        }

        return (new UserAssociation())
            ->setOrganization($this->resolveObject($object))
            ->setUser($this->user);
    }


    /*******************************************
     * DELTA
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->associationQuery()
            ->indexBy('organizationId')
            ->all();

        $associations = [];
        $order = 1;

        /** @var UserAssociation $newAssociation */
        foreach ($this->getRelationships() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getOrganizationId()
            ))) {
                $association = $newAssociation;
            } elseif ($newAssociation->getTypes()->isMutated()) {
                /** @var UserAssociation $association */
                $association->getTypes()->clear()->add(
                    $newAssociation->getTypes()->getCollection()
                );
            }

            $association->userOrder = $newAssociation->userOrder;
            $association->organizationOrder = $order++;
            $association->state = $newAssociation->state;

            $association->ignoreSortOrder();

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }

    /*******************************************
     * COLLECTION
     *******************************************/

    /**
     * Position the relationship based on the sort order
     *
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, UserAssociation $association)
    {
        if ($association->organizationOrder > 0) {
            $collection->splice($association->organizationOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * Reposition the relationship based on the sort order
     *
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, UserAssociation $association)
    {
        if ($key = $this->findKey($association)) {
            $collection->offsetUnset($key);
        }

        $this->insertCollection($collection, $association);
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
        if ($object instanceof UserAssociation) {
            return $this->findRelationshipKey($object->getOrganizationId());
        }

        if (null === ($element = $this->resolveObject($object))) {
            return null;
        }

        return $this->findRelationshipKey($element->getId());
    }

    /**
     * @param $identifier
     * @return int|string|null
     */
    protected function findRelationshipKey($identifier)
    {
        /** @var UserAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->getOrganizationId() == $identifier) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserAssociation|Organization|int|array $organization
     * @return Organization|null
     */
    protected function resolveObjectInternal($organization)
    {
        if ($organization instanceof UserAssociation) {
            return $organization->getOrganization();
        }

        if ($organization instanceof Organization) {
            return $organization;
        }

        return Organization::findOne($organization);
    }
}
