<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\base\ElementInterface;
use Tightenco\Collect\Support\Collection;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
interface RelationshipInterface
{
    /************************************************************
     * FIND
     ************************************************************/

    /**
     * Find a relationship
     *
     * @param ActiveRecord|ElementInterface|int|string|null $object
     * @return ActiveRecord|null
     */
    public function findOne($object = null);

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


    /************************************************************
     * COLLECTIONS
     ************************************************************/

    /**
     * @return Collection|ElementInterface[]
     */
    public function getCollection(): Collection;

    /**
     * @return Collection|ActiveRecord[]
     */
    public function getRelationships(): Collection;

    /************************************************************
     * ADD / REMOVE
     ************************************************************/

    /**
     * Add one or many object relations (but do not save)
     *
     * @param string|int|string[]|int[]|ElementInterface|QueryInterface|Collection|ElementInterface[] $objects
     * @param array $attributes
     * @return static
     */
    public function add($objects, array $attributes = []): RelationshipInterface;

    /**
     * Remove one or many object relations (but do not save)
     *
     * @param string|int|string[]|int[]|ElementInterface|QueryInterface|Collection|ElementInterface[] $objects
     * @return static
     */
    public function remove($objects): RelationshipInterface;


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
     * Clears all current relationships
     */
    public function clear(): RelationshipInterface;

    /**
     * Reset relationships to their original state
     */
    public function reset(): RelationshipInterface;
}
