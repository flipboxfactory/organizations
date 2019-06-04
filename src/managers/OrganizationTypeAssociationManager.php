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
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\OrganizationTypeAssociationQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
class OrganizationTypeAssociationManager
{
    use MutatedTrait;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @var OrganizationTypeAssociation[]|null
     */
    protected $associations;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @param array $criteria
     * @return OrganizationTypeAssociationQuery
     */
    public function query(array $criteria = []): OrganizationTypeAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = OrganizationTypeAssociation::find()
            ->setOrganizationId($this->organization->getId() ?: false)
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
     * @param OrganizationTypeAssociation|OrganizationType|int|string $type
     * @return OrganizationTypeAssociation
     */
    public function create($type): OrganizationTypeAssociation
    {
        return (new OrganizationTypeAssociation())
            ->setOrganization($this->organization)
            ->setType($this->resolveType($type));
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|string $object
     * @return OrganizationTypeAssociation
     */
    public function findOrCreate($object): OrganizationTypeAssociation
    {
        if (null === ($association = $this->findOne($object))) {
            $association = $this->create($object);
        }

        return $association;
    }

    /**
     * @return OrganizationTypeAssociation[]
     */
    public function findAll(): array
    {
        if (null === $this->associations) {
            $this->associations = $this->query()->all();
        }

        return $this->associations;
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|string|null $object
     * @return OrganizationTypeAssociation|null
     */
    public function findOne($object = null)
    {
        if (null === ($key = $this->findKey($object))) {
            return null;
        }

        return $this->associations[$key];
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|string $object
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
     * @param QueryInterface|OrganizationType[] $objects
     * @return $this
     */
    public function setMany($objects)
    {
        if ($objects instanceof QueryInterface) {
            $objects = $objects->all();
        }

        // Reset results
        $this->associations = [];
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
     * @param QueryInterface|OrganizationType[] $objects
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
     * @param OrganizationTypeAssociation|OrganizationType|int|array $object
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
     * Dissociate an array of organization types from an organization
     *
     * @param QueryInterface|OrganizationType[] $objects
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
     * @param OrganizationTypeAssociation|OrganizationType|int|array $object
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
            ->indexBy('typeId')
            ->all();

        $associations = [];
        foreach ($this->findAll() as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getTypeId()
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

        if (!$success) {
            $this->organization->addError('types', 'Unable to save organization types.');
        }

        return $success;
    }

    /*******************************************
     * ASSOCIATE
     *******************************************/

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array $object
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
            $this->organization->addError('types', 'Unable to associate organization type.');

            return false;
        }

        $this->reset();

        return true;
    }

    /**
     * @param QueryInterface|OrganizationType[] $objects
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
     * @param OrganizationTypeAssociation|OrganizationType|int|array $object
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
            $this->organization->addError('types', 'Unable to dissociate organization type.');

            return false;
        }

        $this->removeOne($association);

        return true;
    }

    /**
     * @param QueryInterface|OrganizationType[] $objects
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
     * @param OrganizationTypeAssociation|OrganizationType|int|array|null $type
     * @return int|null
     */
    protected function findKey($type = null)
    {
        if (null === ($record = $this->resolveType($type))) {
            Organizations::info(sprintf(
                "Unable to resolve organization type: %s",
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
     * @param OrganizationTypeAssociation|OrganizationType|int|array|null $type
     * @return OrganizationType|null
     */
    protected function resolveType($type = null)
    {
        if (null === $type) {
            return null;
        }

        if ($type instanceof OrganizationTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof OrganizationType) {
            return $type;
        }

        if (is_array($type) &&
            null !== ($id = ArrayHelper::getValue($type, 'id'))
        ) {
            $type = ['id' => $id];
        }

        return OrganizationType::findOne($type);
    }
}
