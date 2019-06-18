<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserTypeAssociationQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use Tightenco\Collect\Support\Collection;

/**
 * Manages User Types associated to Organization/User associations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property UserTypeAssociation[] $associations
 *
 * @method UserTypeAssociation findOrCreate($object)
 * @method UserTypeAssociation findOne($object = null)
 * @method UserTypeAssociation findOrFail($object)
 */
class UserTypeRelationship implements RelationshipInterface
{
    use RelationshipTrait {
        reset as parentReset;
        newRelations as parentSetCache;
        addToRelations as parentAddToCache;
        removeFromRelations as parentRemoveFromCache;
    }

    /**
     * @var UserAssociation
     */
    private $association;

    /**
     * @param UserAssociation $association
     */
    public function __construct(UserAssociation $association)
    {
        $this->association = $association;
    }


    /************************************************************
     * COLLECTION
     ************************************************************/

    /**
     * Get a collection of associated organizations
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->getRelationships()
            ->sortBy('sortOrder')
            ->pluck('type');
    }

    /**
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        return new Collection(
            $this->query()
                ->with('typeRecord')
                ->all()
        );
    }

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @inheritDoc
     * @return UserTypeAssociationQuery
     */
    protected function query(): UserTypeAssociationQuery
    {
        return UserTypeAssociation::find()
            ->setUserId($this->association->getId() ?: false)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ])
            ->limit(null);
    }

    /**
     * @param UserTypeAssociation|UserType|int|string $type
     * @return UserTypeAssociation
     */
    protected function create($type): UserTypeAssociation
    {
        if ($type instanceof UserTypeAssociation) {
            return $type;
        }

        $association = (new UserTypeAssociation())
            ->setType($this->resolveObject($type));

        $association->userId = $this->association->id;

        return $association;
    }

    /**
     * Reset associations
     */
    public function reset(): RelationshipInterface
    {
        unset($this->association->typeRecords);
        return $this->parentReset();
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('typeId')
            ->all();

        $associations = [];
        $order = 1;
        foreach ($this->getRelationships() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->typeId
            ))) {
                $association = $newAssociation;
            }

            $association->sortOrder = $order++;

            $association->ignoreSortOrder();

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }


    /*******************************************
     * COLLECTION UTILS
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, UserTypeAssociation $association)
    {
        if ($association->sortOrder > 0) {
            $collection->splice($association->sortOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, UserTypeAssociation $association)
    {
        if ($key = $this->findKey($association)) {
            $collection->offsetUnset($key);
        }

        $this->insertCollection($collection, $association);
    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param array $associations
     * @return static
     */
    protected function newRelations(array $associations): self
    {
        $this->parentSetCache($associations);
        $this->syncToRelations();

        return $this;
    }

    /**
     * @param $association
     * @return static
     */
    protected function addToRelations($association): self
    {
        $this->parentAddToCache($association);
        $this->syncToRelations();

        return $this;
    }

    /**
     * @param int $key
     * @return static
     */
    protected function removeFromRelations(int $key): self
    {
        $this->parentRemoveFromCache($key);
        $this->syncToRelations();

        return $this;
    }

    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @return $this
     */
    protected function syncToRelations()
    {
        $this->association->populateRelation(
            'typeRecords',
            $this->getRelationships()->pluck('type')->all()
        );
        return $this;
    }

    /**
     * @param UserTypeAssociation|UserType|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if ($object instanceof OrganizationTypeAssociation) {
            return $this->findRelationshipKey($object->getTypeId());
        }

        if (null === ($type = $this->resolveObject($object))) {
            return null;
        }

        return $this->findRelationshipKey($type->id);
    }

    /**
     * @param $identifier
     * @return int|string|null
     */
    protected function findRelationshipKey($identifier)
    {
        /** @var UserTypeAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->getTypeId() == $identifier) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserTypeAssociation|UserType|int|array|null $type
     * @return UserType|null
     */
    protected function resolveObjectInternal($type)
    {
        if ($type instanceof UserTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof UserType) {
            return $type;
        }

        return UserType::findOne($type);
    }
}
