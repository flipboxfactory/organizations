<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use flipbox\organizations\records\UserAssociation;
use Tightenco\Collect\Support\Collection;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @mixin RelationshipInterface
 */
trait RelationshipTrait
{
    use MutatedTrait;

    /**
     * @var Collection|null
     */
    protected $relations;

    /**
     * @param null $object
     * @return int|null
     */
    abstract protected function findKey($object = null);

    /**
     * Create a new association/relationship record
     *
     * @param $object
     * @return ActiveRecord
     */
    abstract protected function create($object): ActiveRecord;

    /**
     * Resolve the object that will be related
     *
     * @param $object
     * @return mixed
     */
    abstract protected function resolveObjectInternal($object);

    /**
     * A collection of existing relationships, indexed by id.  We'll compare these with
     * and new relations to determine if we need to add/remove relations
     *
     * @return Collection
     */
    abstract protected function existingRelationships(): Collection;

    /**
     * An array of relationships to save and an array of relationships to delete.  This will
     * want to be used as such: `list($save, $delete) = $this->delta();`
     *
     * @return array
     */
    abstract protected function delta(): array;

    /**
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return ActiveRecord
     */
    public function findOrCreate($object): ActiveRecord
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
        }

        return $association;
    }

    /**
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return ActiveRecord
     * @throws Exception
     */
    public function findOrFail($object): ActiveRecord
    {
        if (null === ($association = $this->findOne($object))) {
            throw new Exception("Association could not be found.");
        }

        return $association;
    }

    /**
     * @param ActiveRecord|ElementInterface|int|string|null $object
     * @return ActiveRecord|null
     */
    public function findOne($object = null)
    {
        if (null === ($key = $this->findKey($object))) {
            return null;
        }

        return $this->getRelationships()->get($key);
    }


    /************************************************************
     * COLLECTIONS
     ************************************************************/

    /**
     * @inheritDoc
     */
    public function getRelationships(): Collection
    {
        if (null === $this->relations) {
            $this->relations = $this->existingRelationships();
        }

        return $this->relations;
    }


    /************************************************************
     * ADD / REMOVE
     ************************************************************/

    /**
     * Add one or many object relations (but do not save)
     *
     * @param $objects
     * @param array $attributes
     * @return RelationshipInterface
     */
    public function add($objects, array $attributes = []): RelationshipInterface
    {
        foreach ($this->objectArray($objects) as $object) {
            $this->addOne($object, $attributes);
        }

        return $this;
    }

    /**
     * @param $object
     * @param array $attributes
     * @return RelationshipInterface
     */
    private function addOne($object, array $attributes = []): RelationshipInterface
    {
        $isNew = false;

        // Check if it's already linked
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
            $isNew = true;
        }

        // Modify?
        if (!empty($attributes)) {
            Craft::configure(
                $association,
                $attributes
            );

            $this->mutated = true;

            if (!$isNew) {
                $this->updateCollection($this->relations, $association);
            }
        }

        if ($isNew) {
            $this->addToRelations($association);
        }

        return $this;
    }

    /**
     * @param $objects
     * @return RelationshipInterface
     */
    public function remove($objects): RelationshipInterface
    {
        foreach ($this->objectArray($objects) as $object) {
            if (null !== ($key = $this->findKey($object))) {
                $this->removeFromRelations($key);
            }
        }

        return $this;
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @return bool
     */
    public function save(): bool
    {
        // No changes?
        if (!$this->isMutated()) {
            return true;
        }

        $success = true;

        list($save, $delete) = $this->delta();

        foreach ($delete as $relationship) {
            if (!$relationship->delete()) {
                $success = false;
            }
        }

        foreach ($save as $relationship) {
            if (!$relationship->save()) {
                $success = false;
            }
        }

        $this->reset();

        return $success;
    }


    /************************************************************
     * UTILITIES
     ************************************************************/

    /**
     * Reset associations
     * @return RelationshipInterface
     */
    public function reset(): RelationshipInterface
    {
        $this->relations = null;
        $this->mutated = false;
        return $this;
    }

    /**
     * Reset associations
     * @return RelationshipInterface
     */
    public function clear(): RelationshipInterface
    {
        $this->newRelations([]);
        return $this;
    }

    /**
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return bool
     */
    public function exists($object): bool
    {
        return null !== $this->findKey($object);
    }


    /*******************************************
     * COLLECTION UTILS
     *******************************************/

    /**
     * @param array $associations
     * @param bool $mutated
     * @return static
     */
    protected function newRelations(array $associations, bool $mutated = true): self
    {
        $this->relations = $this->createRelations($associations);
        $this->mutated = $mutated;

        return $this;
    }

    /**
     * @param $association
     * @return RelationshipTrait
     */
    protected function addToRelations($association): self
    {
        if (null === $this->relations) {
            return $this->newRelations([$association], true);
        }

        $this->insertCollection($this->relations, $association);
        $this->mutated = true;

        return $this;
    }

    /**
     * @param int $key
     * @return RelationshipTrait
     */
    protected function removeFromRelations(int $key): self
    {
        $this->relations->forget($key);
        $this->mutated = true;

        return $this;
    }

    /**
     * @param array $associations
     * @return Collection
     */
    protected function createRelations(array $associations = []): Collection
    {
        $collection = new Collection();
        foreach ($associations as $association) {
            $this->insertCollection($collection, $association);
        }

        return $collection;
    }

    /**
     * Update a relation that's already association.
     *
     * @param Collection $collection
     * @param $association
     */
    protected function updateCollection(Collection $collection, $association)
    {
    }

    /**
     * Insert a relation that's not already associated
     *
     * @param Collection $collection
     * @param $association
     */
    protected function insertCollection(Collection $collection, $association)
    {
        $collection->push($association);
    }


    /*******************************************
     * RESOLVERS
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function resolveObject($object = null)
    {
        if (null === $object) {
            return null;
        }

        if (is_array($object) &&
            null !== ($id = ArrayHelper::getValue($object, 'id'))
        ) {
            $object = ['id' => $id];
        }

        return $this->resolveObjectInternal($object);
    }

    /**
     * Ensure we're working with an array of objects, not configs, etc
     *
     * @param array|QueryInterface|Collection|ElementInterface|UserAssociation $objects
     * @return array
     */
    protected function objectArray($objects): array
    {
        if ($objects instanceof QueryInterface || $objects instanceof Collection) {
            $objects = $objects->all();
        }

        // proper array
        if (!is_array($objects) || ArrayHelper::isAssociative($objects)) {
            $objects = [$objects];
        }

        return array_filter($objects);
    }
}
