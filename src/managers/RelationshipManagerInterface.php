<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use craft\base\ElementInterface;
use Tightenco\Collect\Support\Collection;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
interface RelationshipManagerInterface
{
    /************************************************************
     * FIND
     ************************************************************/

    /**
     * Find a relationship or create a new one
     *
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return ActiveRecord
     */
    public function findOrCreate($object): ActiveRecord;

    /**
     * Find a relationship or throw an exception if not found
     *
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return ActiveRecord
     * @throws Exception
     */
    public function findOrFail($object): ActiveRecord;

    /**
     * Find an array of relationships
     *
     * @return Collection
     */
    public function findAll(): Collection;

    /**
     * Find a relationship
     *
     * @param ActiveRecord|ElementInterface|int|string|null $object
     * @return ActiveRecord|null
     */
    public function findOne($object = null);


    /************************************************************
     * SET
     ************************************************************/

    /**
     * Clear the current relationships and set new ones.
     *
     * @param QueryInterface|Collection|ElementInterface[] $objects
     * @param array $attributes
     * @return static
     */
    public function setMany($objects, array $attributes = []): RelationshipManagerInterface;


    /************************************************************
     * ADD
     ************************************************************/

    /**
     * Add an array of objects (but do not save)
     *
     * @param QueryInterface|Collection|ElementInterface[] $objects
     * @param array $attributes
     * @return static
     */
    public function addMany($objects, array $attributes = []): RelationshipManagerInterface;

    /**
     * Add an object (but do not save)
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @param array $attributes
     * @return static
     */
    public function addOne($object, array $attributes = []): RelationshipManagerInterface;


    /************************************************************
     * REMOVE
     ************************************************************/

    /**
     * Remove an array of objects  (but do not save)
     *
     * @param QueryInterface|Collection|ElementInterface[] $objects
     * @return static
     */
    public function removeMany($objects): RelationshipManagerInterface;

    /**
     * Remove an object (but do not save)
     *
     * @param ActiveRecord|ElementInterface|int|array
     * @return static
     */
    public function removeOne($object): RelationshipManagerInterface;


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * Save the current collection of relationships.  This should update existing relationships, create
     * new relationships and delete abandoned relationships.
     *
     * @return bool
     */
    public function save(): bool;


    /*******************************************
     * ASSOCIATE
     *******************************************/

    /**
     * Upsert a relationship (and save)
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @param array $attributes
     * @return bool
     */
    public function associateOne($object, array $attributes = []): bool;

    /**
     * Upsert an array of relationship (and save)
     *
     * @param QueryInterface|Collection|ElementInterface[] $objects
     * @return bool
     */
    public function associateMany($objects): bool;


    /*******************************************
     * DISSOCIATE
     *******************************************/

    /**
     * Delete an relationship (and save)
     *
     * @param ActiveRecord|ElementInterface|int|array $object
     * @return bool
     */
    public function dissociateOne($object): bool;

    /**
     * Delete an array of relationships (and save)
     *
     * @param QueryInterface|Collection|ElementInterface[] $objects
     * @return bool
     */
    public function dissociateMany($objects): bool;


    /************************************************************
     * UTILS
     ************************************************************/

    /**
     * Check if a relationship already exists
     *
     * @param ActiveRecord|ElementInterface|int|string $object
     * @return bool
     */
    public function exists($object): bool;

    /**
     * Check if the relationships have been altered
     *
     * @return bool
     */
    public function isMutated(): bool;

    /**
     * Reset relationships to their original state
     */
    public function reset(): RelationshipManagerInterface;
}
