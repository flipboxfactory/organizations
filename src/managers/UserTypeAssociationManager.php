<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserTypeAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use yii\db\QueryInterface;

/**
 * This class provides an interface to manage user type associations.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
class UserTypeAssociationManager
{
    use MutatedTrait;

    /**
     * @var UserAssociation
     */
    private $association;

    /**
     * @var UserTypeAssociation[]|null
     */
    protected $associations;

    /**
     * @param UserAssociation $association
     */
    public function __construct(UserAssociation $association)
    {
        $this->association = $association;
    }

    /**
     * @return UserTypeAssociationQuery
     */
    public function query(): UserTypeAssociationQuery
    {
        $query = UserTypeAssociation::find()
            ->setUserId($this->association->getId() ?: false)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * @param UserTypeAssociation|UserType|int|string $type
     * @return UserTypeAssociation
     */
    public function create($type): UserTypeAssociation
    {
        $association = (new UserTypeAssociation())
            ->setType($this->resolveType($type));

        $association->userId = $this->association->id;

        return $association;
    }

    /**
     * @param UserTypeAssociation|UserType|int|string $object
     * @return UserTypeAssociation
     */
    public function findOrCreate($object): UserTypeAssociation
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
        }

        return $association;
    }

    /**
     * @return UserTypeAssociation[]
     */
    public function findAll(): array
    {
        if (null === $this->associations) {
            $this->associations = $this->query()->all();
            $this->syncToRelations();
        }

        return $this->associations;
    }

    /**
     * @param UserTypeAssociation|UserType|int|string|null $object
     * @return UserTypeAssociation|null
     */
    public function findOne($object = null)
    {
        if (null === ($key = $this->findKey($object))) {
            return null;
        }

        return $this->associations[$key];
    }

    /**
     * @param UserTypeAssociation|UserType|int|string $object
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
     * @param QueryInterface|UserType[] $objects
     * @return $this
     */
    public function setMany($objects)
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        // Reset results
        $this->associations = [];
        $this->syncToRelations();
        $this->mutated = true;

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
     * @param QueryInterface|UserType[] $objects
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
     * Associate an organization type to an organization
     *
     * @param UserTypeAssociation|UserType|int|array $object
     * @return $this
     */
    public function addOne($object)
    {
        if (null === ($association = $this->findOne($object))) {
            $this->associations[] = $this->create($object);
            $this->syncToRelations();
            $this->mutated = true;
        }

        return $this;
    }

    /************************************************************
     * REMOVE
     ************************************************************/

    /**
     * Dissociate an array of organization types from an organization
     *
     * @param QueryInterface|UserType[] $objects
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
     * Dissociate a organization type from an organization
     *
     * @param UserTypeAssociation|UserType|int|array
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
     * @return $this
     */
    protected function syncToRelations()
    {
        $this->association->populateRelation('types', ArrayHelper::getColumn(
            $this->findAll(),
            'type'
        ));
        return $this;
    }

    /**
     * Reset associations
     */
    public function reset()
    {
        unset($this->association->types);
        $this->associations = null;
        $this->mutated = false;
        return $this;
    }

    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function save(): bool
    {
        // No changes?
        if (!$this->isMutated()) {
            return true;
        }

        $success = true;

        $existingAssociations = $this->query()
            ->indexBy('userId')
            ->all();

        $associations = [];
        foreach ($this->findAll() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->userId
            ))) {
                $association = $newAssociation;
            }

            $association->sortOrder = $newAssociation->sortOrder;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($existingAssociations as $existingAssociation) {
            if (!$existingAssociation->delete()) {
                $success = false;
            }
        }

        $order = 1;
        foreach ($associations as $association) {
            $association->sortOrder = $order++;

            if (!$association->save()) {
                $success = false;
            }
        }

        $this->associations = $associations;
        $this->syncToRelations();

        if (!$success) {
            $this->association->addError('types', 'Unable to save organization types.');
        }

        return $success;
    }

    /*******************************************
     * ASSOCIATE
     *******************************************/

    /**
     * @param UserTypeAssociation|UserType|int|array $object
     * @param int|null $sortOrder
     * @return bool
     */
    public function associateOne($object, int $sortOrder = null): bool
    {
        $association = $this->findOrCreate($object);

        if (null !== $sortOrder) {
            $association->sortOrder = $sortOrder;
        }

        if (!$association->save()) {
            $this->association->addError('types', 'Unable to associate user type.');

            return false;
        }

        $this->reset();

        return true;
    }

    /**
     * @param QueryInterface|UserType[] $objects
     * @return bool
     * @throws \Throwable
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
     * @param UserTypeAssociation|UserType|int|array $object
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociateOne($object): bool
    {
        if (null === ($association = $this->findOne($object))) {
            return true;
        }

        if (!$association->delete()) {
            $this->association->addError('types', 'Unable to dissociate organization type.');

            return false;
        }

        $this->removeOne($association);

        return true;
    }

    /**
     * @param QueryInterface|UserType[] $objects
     * @return bool
     * @throws \Throwable
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
     * UTILS
     *******************************************/

    /**
     * @param UserTypeAssociation|UserType|int|array|null $type
     * @return int|null
     */
    protected function findKey($type = null)
    {
        if (null === ($record = $this->resolveType($type))) {
            Organizations::info(sprintf(
                "Unable to resolve user association type: %s",
                (string)Json::encode($type)
            ));
            return null;
        }

        foreach ($this->findAll() as $key => $association) {
            if ($association->getTypeId() == $record->id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserTypeAssociation|UserType|int|array|null $type
     * @return UserType|null
     */
    protected function resolveType($type = null)
    {
        if (null === $type) {
            return null;
        }

        if ($type instanceof UserTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof UserType) {
            return $type;
        }

        if (is_array($type) &&
            null !== ($id = ArrayHelper::getValue($type, 'id'))
        ) {
            $type = ['id' => $id];
        }

        return UserType::findOne($type);
    }
}
