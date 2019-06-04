<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.00
 */
trait AssociationManagerTrait
{
    use MutatedTrait;

    /**
     * @var ActiveRecord[]|null
     */
    protected $associations;

    /**
     * @param null $object
     * @return int|null
     */
    abstract protected function findKey($object = null);

    /**
     * @param array $criteria
     * @return QueryInterface
     */
    abstract public function query(array $criteria = []): QueryInterface;

    /**
     * @param $object
     * @return ActiveRecord
     */
    abstract public function create($object): ActiveRecord;

    /**
     *
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
     * @return ActiveRecord[]
     */
    public function findAll(): array
    {
        if (null === $this->associations) {
            $this->setCache($this->query()->all());
        }

        return $this->associations;
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

        return $this->associations[$key];
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
     * SET
     ************************************************************/

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return static
     */
    public function setMany($objects)
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        // Reset results
        $this->setCache([]);

        if (!empty($objects)) {
            if (!is_array($objects)) {
                $objects = [$objects];
            }

            $this->addMany($objects);
        }

        return $this;
    }


    /************************************************************
     * ADD
     ************************************************************/

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return static
     */
    public function addMany($objects)
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        if (!is_array($objects)) {
            $objects = [$objects];
        }

        // In case a config is directly passed
        if (ArrayHelper::isAssociative($objects)) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->addOne($object);
        }

        return $this;
    }

    /**
     * Associate a user to an organization
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @param array $attributes
     * @return static
     */
    public function addOne($object, array $attributes = [])
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

        return $this;
    }


    /************************************************************
     * REMOVE
     ************************************************************/

    /**
     * Dissociate an array of user associations from an organization
     *
     * @param QueryInterface|ElementInterface[] $objects
     * @return static
     */
    public function removeMany($objects)
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        if (!is_array($objects)) {
            $objects = [$objects];
        }

        // In case a config is directly passed
        if (ArrayHelper::isAssociative($objects)) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->removeOne($object);
        }

        return $this;
    }

    /**
     * Dissociate a user from an organization
     *
     * @param ActiveRecord|ElementInterface|int|array
     * @return static
     */
    public function removeOne($object)
    {
        if (null !== ($key = $this->findKey($object))) {
            $this->removeFromCache($key);
        }

        return $this;
    }

    /**
     * Reset associations
     */
    public function reset()
    {
        $this->associations = null;
        $this->mutated = false;
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

        $this->setCache($newAssociations);
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
     */
    public function associateOne($object, array $attributes = []): bool
    {
        $association = $this->findOrCreate($object);

        if (!empty($attributes)) {
            Craft::configure(
                $association,
                $attributes
            );
        }

        if (!$association->save()) {
            $this->handleAssociationError();
            return false;
        }

        $this->reset();

        return true;
    }

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return bool
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
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @return bool
     */
    public function dissociateOne($object): bool
    {
        if (null === ($association = $this->findOne($object))) {
            return true;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        if (!$association->delete()) {
            $this->handleAssociationError();
            return false;
        }

        $this->removeOne($association);

        return true;
    }

    /**
     * @param QueryInterface|ElementInterface[] $objects
     * @return bool
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


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param array $associations
     * @return static
     */
    protected function setCache(array $associations): self
    {
        $this->associations = $associations;
        $this->mutated = true;

        return $this;
    }

    /**
     * @param $association
     * @return AssociationManagerTrait
     */
    protected function addToCache($association): self
    {
        $this->associations[] = $association;
        $this->mutated = true;

        return $this;
    }

    /**
     * @param int $key
     * @return AssociationManagerTrait
     */
    protected function removeFromCache(int $key): self
    {
        unset($this->associations[$key]);
        $this->mutated = true;

        return $this;
    }
}
