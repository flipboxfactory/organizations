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
    protected $collection;

    /**
     * @param null $object
     * @return int|null
     */
    abstract protected function findKey($object = null);

    /**
     * @param array $criteria
     * @return QueryInterface
     */
    abstract protected function query(array $criteria = []): QueryInterface;

    /**
     * @param $object
     * @return ActiveRecord
     */
    abstract protected function create($object): ActiveRecord;

    /**
     * @return ActiveRecord[][]
     */
    abstract protected function associationDelta(): array;

    /**
     * @return void
     */
    abstract protected function handleAssociationError();

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
     * @return Collection
     *
     * @deprecated use `getCollection()`
     */
    public function findAll(): Collection
    {
        return $this->getRelationships();
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

        return $this->collection->get($key);
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
        if (null === $this->collection) {
            $this->collection = new Collection(
                $this->query()->all()
            );
        }

        return $this->collection;
    }


    /************************************************************
     * SET
     ************************************************************/

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @param array $attributes
     * @return RelationshipInterface
     *
     * @deprecated use `reset()->add($objects, $attributes)`
     */
    public function setMany($objects, array $attributes = []): RelationshipInterface
    {
        return $this->clear()
            ->add($objects, $attributes);
    }


    /************************************************************
     * ADD
     ************************************************************/

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @param array $attributes
     * @return RelationshipInterface
     *
     * @deprecated use `add($objects, $attributes)`
     */
    public function addMany($objects, array $attributes = []): RelationshipInterface
    {
        return $this->add($objects, $attributes);
    }

    /**
     * Associate a user to an organization
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @param array $attributes
     * @return RelationshipInterface
     *
     * @deprecated use `add($objects, $attributes)`
     */
    public function addOne($object, array $attributes = []): RelationshipInterface
    {
        return $this->add($object, $attributes);
    }

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
                $this->addToCollection($association);
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


    /************************************************************
     * REMOVE
     ************************************************************/

    /**
     * Dissociate an array of user associations from an organization
     *
     * @param QueryInterface|ElementInterface[] $objects
     * @return RelationshipInterface
     *
     * @deprecated use `remove($objects)`
     */
    public function removeMany($objects): RelationshipInterface
    {
        return $this->remove($objects);
    }

    /**
     * Dissociate a user from an organization
     *
     * @param ActiveRecord|ElementInterface|int|array
     * @return RelationshipInterface
     *
     * @deprecated use `remove($objects)`
     */
    public function removeOne($object): RelationshipInterface
    {
        return $this->remove($object);
    }

    /**
     * @param $objects
     * @return RelationshipInterface
     */
    public function remove($objects): RelationshipInterface
    {
        foreach ($this->objectArray($objects) as $object) {
            if (null !== ($key = $this->findKey($object))) {
                $this->removeFromCollection($key);
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
        $this->collection = null;
        $this->mutated = false;
        return $this;
    }

    /**
     * Reset associations
     * @return RelationshipInterface
     */
    public function clear(): RelationshipInterface
    {
        $this->newCollection([]);
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

        list($newAssociations, $existingAssociations) = $this->associationDelta();

        // Delete those removed
        foreach ($existingAssociations as $existingAssociation) {
            if (!$existingAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($newAssociations as $newAssociation) {
            if (!$newAssociation->save()) {
                $success = false;
            }
        }

        $this->newCollection($newAssociations);
        $this->mutated = false;

        if (!$success) {
            $this->handleAssociationError();
        }

        return $success;
    }


    /*******************************************
     * ASSOCIATE
     *******************************************/

    /**
     * @param $object
     * @param array $attributes
     * @return bool
     *
     * @deprecated use `add($object, $attributes)->save()`
     */
    public function associateOne($object, array $attributes = []): bool
    {
        return $this->add($object, $attributes)
            ->save();
    }

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return bool
     *
     * @deprecated use `add($object, $attributes)->save()`
     */
    public function associateMany($objects): bool
    {
        return $this->add($objects)
            ->save();
    }


    /*******************************************
     * DISSOCIATE
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @return bool
     *
     * @deprecated use `remove($object)->save()`
     */
    public function dissociateOne($object): bool
    {
        return $this->remove($object)
            ->save();
    }

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return bool
     *
     * @deprecated use `remove($objects)->save()`
     */
    public function dissociateMany($objects): bool
    {
        return $this->remove($objects)
            ->save();
    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param array $associations
     * @param bool $mutated
     * @return static
     */
    protected function newCollection(array $associations, bool $mutated = true): self
    {
        $this->collection = Collection::make($associations);
        $this->mutated = $mutated;

        return $this;
    }

    /**
     * @param $association
     * @return RelationshipTrait
     */
    protected function addToCollection($association): self
    {
        if (null === $this->collection) {
            return $this->newCollection([$association], true);
        }

        $this->collection->push($association);
        $this->mutated = true;

        return $this;
    }

    /**
     * @param int $key
     * @return RelationshipTrait
     */
    protected function removeFromCollection(int $key): self
    {
        $this->collection->forget($key);
        $this->mutated = true;

        return $this;
    }


    /*******************************************
     * UTILITIES
     *******************************************/

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
