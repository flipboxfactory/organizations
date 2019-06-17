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
    abstract protected function resolveObject($object);

    /**
     * A collection of existing relationships, indexed by id.  We'll compare these with
     * and new relations to determine if we need to add/remove relations
     *
     * @return Collection
     */
    abstract protected function existingRelationships(): Collection;

    /**
     *
     * @return Collection[]
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

        return $this->relations->get($key);
    }

    /**
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return bool
     */
    public function exists($object): bool
    {
        return null !== $this->findKey($object);
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
            if (null === ($association = $this->findOne($object))) {
                $association = $this->create($object);
                $this->addToRelations($association);
            }

            if (!empty($attributes)) {
                Craft::configure(
                    $association,
                    $attributes
                );

                $this->mutated = true;
            }
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


    /************************************************************
     * RESET
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

        $this->newRelations($save, false);

        return $success;
    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param array $associations
     * @param bool $mutated
     * @return static
     */
    protected function newRelations(array $associations, bool $mutated = true): self
    {
        $this->relations = Collection::make($associations);
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

        $this->relations->push($association);
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


    /*******************************************
     * UTILITIES
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function resolve($object = null)
    {
        if (null === $object) {
            return null;
        }

        if (is_array($object) &&
            null !== ($id = ArrayHelper::getValue($object, 'id'))
        ) {
            $object = ['id' => $id];
        }

        return $this->resolveObject($object);
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
