<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use DateTime;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use yii\db\QueryInterface;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
trait UserAssociationManagerTrait
{
    use MutateableTrait;

    /**
     * @var UserAssociation[]|null
     */
    protected $associations;

    /**
     * @param null $object
     * @return int|null
     */
    abstract protected function findKey($object = null);

    /**
     * @param array $criteria
     * @return UserAssociationQuery
     */
    abstract public function query(array $criteria = []): UserAssociationQuery;

    /**
     * @param $object
     * @return UserAssociation
     */
    abstract public function create($object): UserAssociation;

    /**
     * @param UserAssociation|ElementInterface|int|string $object
     * @return UserAssociation
     */
    public function findOrCreate($object): UserAssociation
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
        }

        return $association;
    }

    /**
     * @return UserAssociation[]
     */
    public function findAll(): array
    {
        if (null === $this->associations) {
            $this->associations = $this->query()->all();
        }

        return $this->associations;
    }

    /**
     * @param UserAssociation|ElementInterface|int|string|null $object
     * @return UserAssociation|null
     */
    public function findOne($object = null)
    {
        if (null === ($key = $this->findKey($object))) {
            return null;
        }

        return $this->associations[$key];
    }

    /**
     * @param UserAssociation|ElementInterface|int|string $object
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
     * @param $objects
     * @return $this
     */
    public function setMany($objects)
    {
        if ($objects instanceof UserAssociationQuery) {
            $this->associations = $objects->all();
            return $this;
        }

        // Reset results
        $this->associations = [];

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
     * @return $this
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
     * @param UserAssociation|ElementInterface|int|array $object
     * @return $this
     */
    public function addOne($object)
    {
        if (null === ($association = $this->findOne($object))) {
            $this->associations[] = $this->create($object);

            $this->mutated = true;
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
     * @return $this
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
     * @param UserAssociation|ElementInterface|int|array
     * @return $this
     */
    public function removeOne($object)
    {
        if (null !== ($key = $this->findKey($object))) {
            unset($this->associations[$key]);
            $this->mutated = true;
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
}
