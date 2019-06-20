<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\elements\User;
use craft\helpers\ArrayHelper;
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

        return $this->createCollectionFromRelations();
    }

    /**
     * @return Collection
     */
    protected function createCollectionFromRelations()
    {
        $ids = $this->getRelationships()->pluck('organizationId')->all();
        if (empty($ids)) {
            return $this->getRelationships()->pluck('organization');
        }

        // 'eager' load where we'll pre-populate all of the elements
        $elements = $this->elementQuery()
            ->id($ids)
            ->indexBy('id')
            ->all();

        return $this->getRelationships()
            ->transform(function (UserAssociation $association) use ($elements) {
                if (!$association->isOrganizationSet() && isset($elements[$association->getOrganizationId()])) {
                    $association->setOrganization($elements[$association->getOrganizationId()]);
                }

                $association->setUser($this->user);

                return $association;
            })
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

        return $this->createRelations($relationships);
    }

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return OrganizationQuery
     */
    private function elementQuery(): OrganizationQuery
    {
        return Organization::find()
            ->userId($this->user->getId() ?: false)
            ->anyStatus()
            ->limit(null);
    }

    /**
     * @return UserAssociationQuery
     */
    private function associationQuery(): UserAssociationQuery
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
            $association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getOrganizationId()
            );

            $newAssociation->organizationOrder = $order++;

            /** @var UserAssociation $association */
            $association = $association ?: $newAssociation;

            // Has anything changed?
            if (!$association->getIsNewRecord() && !$this->hasChanged($newAssociation, $association)) {
                continue;
            }

            $associations[] = $this->sync($association, $newAssociation);
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @param UserAssociation $new
     * @param UserAssociation $existing
     * @return bool
     */
    private function hasChanged(UserAssociation $new, UserAssociation $existing): bool
    {
        return (Organizations::getInstance()->getSettings()->getEnforceUserSortOrder() &&
                $new->organizationOrder != $existing->organizationOrder) ||
            $new->state != $existing->state ||
            $new->getTypes()->isMutated();
    }

    /**
     * @param UserAssociation $from
     * @param UserAssociation $to
     *
     * @return UserAssociation
     */
    private function sync(UserAssociation $to, UserAssociation $from): UserAssociation
    {
        $to->organizationOrder = $from->organizationOrder;
        $to->state = $from->state;

        if ($from->getTypes()->isMutated()) {
            $to->getTypes()->clear()->add(
                $from->getTypes()->getCollection()
            );
        }

        $to->ignoreSortOrder();

        return $to;
    }

    /*******************************************
     * COLLECTION UTILS
     *******************************************/

    /**
     * Position the relationship based on the sort order
     *
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, UserAssociation $association)
    {
        if (Organizations::getInstance()->getSettings()->getEnforceUserSortOrder() &&
            $association->organizationOrder > 0
        ) {
            $collection->splice($association->organizationOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, UserAssociation $association)
    {
        if (!Organizations::getInstance()->getSettings()->getEnforceUserSortOrder()) {
            return;
        }

        if (null !== ($key = $this->findKey($association))) {
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
    private function findRelationshipKey($identifier)
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
