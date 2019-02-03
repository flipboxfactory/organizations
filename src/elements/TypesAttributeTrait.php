<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\queries\OrganizationTypeQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationType as TypeModel;
use flipbox\organizations\records\OrganizationTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TypesAttributeTrait
{
    /**
     * @var OrganizationTypeQuery
     */
    private $types;

    /**
     * @var TypeModel|false
     */
    private $activeType;

    /************************************************************
     * REQUEST
     ************************************************************/

    /**
     * AssociateUserToOrganization an array of types from request input
     *
     * @param string $identifier
     * @return $this
     */
    public function setTypesFromRequest(string $identifier = 'types')
    {
        if (null !== ($types = Craft::$app->getRequest()->getBodyParam($identifier))) {
            $this->setTypes((array) $types);
        }

        return $this;
    }

    /************************************************************
     * ACTIVE TYPE
     ************************************************************/

    /**
     * @param TypeModel|null $type
     * @return $this
     */
    public function setActiveType(TypeModel $type = null)
    {
        if ($type) {
            $this->addType($type);
        }

        $this->activeType = (null === $type) ? false : $type;
        return $this;
    }

    /**
     * @return TypeModel|null
     */
    public function getActiveType()
    {
        if (null === $this->activeType) {
            if (!$activeType = $this->getPrimaryType()) {
                $activeType = false;
            }

            $this->activeType = $activeType;
        }

        return (false === $this->activeType) ? null : $this->activeType;
    }

    /************************************************************
     * TYPES
     ************************************************************/

    /**
     * Get an array of types associated to an organization
     *
     * @param array $criteria
     * @return OrganizationTypeQuery
     */
    public function getTypes($criteria = [])
    {
        if (null === $this->types) {
            $this->types = OrganizationType::find()
                ->organization($this);
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->types,
                $criteria
            );
        }

        return $this->types;
    }

    /**
     * AssociateUserToOrganization types to an organization
     *
     * @param $types
     * @return $this
     */
    public function setTypes($types)
    {
        if ($types instanceof OrganizationTypeQuery) {
            $this->types = $types;
            return $this;
        }

        // Reset the query
        $this->types = OrganizationType::find()
            ->organization($this);

        // Remove all types
        $this->types->setCachedResult([]);

        if (!empty($types)) {
            if (!is_array($types)) {
                $types = [$types];
            }

            $this->addTypes($types);
        }

        return $this;
    }

    /**
     * AssociateUserToOrganization an array of types to an organization
     *
     * @param $types
     * @return $this
     */
    public function addTypes(array $types)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($types)) {
            $types = [$types];
        }

        foreach ($types as $key => $type) {
            // Ensure we have a model
            if (!$type instanceof OrganizationType) {
                $type = $this->resolveType($type);
            }

            $this->addType($type);
        }

        return $this;
    }

    /**
     * AssociateUserToOrganization a type to an organization
     *
     * @param OrganizationType $type
     * @return $this
     */
    public function addType(OrganizationType $type)
    {
        $currentTypes = $this->getTypes()->all();

        $indexedTypes = ArrayHelper::index(
            $currentTypes,
            'handle'
        );

        if (!array_key_exists($type->handle, $indexedTypes)) {
            $currentTypes[] = $type;
            $this->getTypes()->setCachedResult($currentTypes);
        }

        return $this;
    }

    /**
     * DissociateUserFromOrganization a type from an organization
     *
     * @param array $types
     * @return $this
     */
    public function removeTypes(array $types)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($types)) {
            $types = [$types];
        }

        foreach ($types as $key => $type) {
            if (!$type instanceof OrganizationType) {
                $type = $this->resolveType($type);
            }

            $this->removeType($type);
        }

        return $this;
    }

    /**
     * @param mixed $type
     * @return OrganizationType
     */
    protected function resolveType($type): OrganizationType
    {
        if (null !== ($type = OrganizationType::findOne($type))) {
            return $type;
        }

        if (!is_array($type)) {
            $type = ArrayHelper::toArray($type, [], false);
        }

        return new OrganizationType($type);
    }

    /**
     * DissociateUserFromOrganization a type from an organization
     *
     * @param OrganizationType $type
     * @return $this
     */
    public function removeType(OrganizationType $type)
    {
        $indexedTypes = ArrayHelper::index(
            $this->getTypes()->all(),
            'handle'
        );

        // Does the type already exist?
        if (array_key_exists($type->handle, $indexedTypes)) {
            unset($indexedTypes[$type->handle]);

            $this->getTypes()->setCachedResult(
                array_values($indexedTypes)
            );
        }

        return $this;
    }

    /**
     * Reset types
     *
     * @return $this
     */
    public function resetTypes()
    {
        $this->types = null;
        return $this;
    }

    /**
     * Get an associated type by identifier (id/handle)
     *
     * @param $identifier
     * @return null|TypeModel
     */
    public function getType($identifier)
    {
        // Determine index type
        $indexBy = (is_numeric($identifier)) ? 'id' : 'handle';

        // Find all types
        $allTypes = ArrayHelper::index(
            $this->getTypes()->all(),
            $indexBy
        );

        return array_key_exists($identifier, $allTypes) ? $allTypes[$identifier] : null;
    }

    /**
     * Identify whether a type is associated to the element
     *
     * @param TypeModel|null $type
     * @return bool
     */
    public function hasType(TypeModel $type = null): bool
    {
        if (null === $type) {
            return !empty($this->getTypes());
        }

        return null !== $this->getType($type->id);
    }


    /************************************************************
     * PRIMARY TYPE
     ************************************************************/

    /**
     * Identify whether a primary type is set
     *
     * @return bool
     */
    public function hasPrimaryType()
    {
        return $this->getTypes()->one() instanceof TypeModel;
    }

    /**
     * Identify whether the type is primary
     *
     * @param $type
     * @return bool
     */
    public function isPrimaryType(TypeModel $type)
    {
        if ($primaryType = $this->getPrimaryType()) {
            return $primaryType->id === $type->id;
        }

        return false;
    }

    /**
     * @param TypeModel $type
     * @return $this
     */
    public function setPrimaryType(TypeModel $type = null)
    {
        if (null === $type) {
            return $this;
        }

        return $this->setTypes(
            array_merge(
                [
                    $type
                ],
                $this->getTypes()->all()
            )
        );
    }

    /**
     * Get the primary type
     *
     * @return TypeModel|null
     */
    public function getPrimaryType()
    {
        if (!$this->hasPrimaryType()) {
            return null;
        }

        return $this->getTypes()->one();
    }

    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\db\StaleObjectException
     */
    public function saveTypes(): bool
    {
        // No changes?
        if (null === ($types = $this->getTypes()->getCachedResult())) {
            return true;
        }

        $currentAssociations = OrganizationTypeAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;
        $associations = [];
        $order = 1;
        foreach ($types as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                $association = (new OrganizationTypeAssociation())
                    ->setType($type)
                    ->setOrganization($this);
            }

            $association->sortOrder = $order++;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($currentAssociations as $currentAssociation) {
            if (!$currentAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($associations as $association) {
            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('types', 'Unable to associate types.');
        }

        return $success;
    }

    /**
     * @param OrganizationTypeQuery $query
     * @return bool
     * @throws \Throwable
     */
    public function associateTypes(OrganizationTypeQuery $query): bool
    {
        $types = $query->all();

        if (empty($types)) {
            return true;
        }

        $currentAssociations = OrganizationTypeAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;
        foreach ($types as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                $association = (new OrganizationTypeAssociation())
                    ->setType($type)
                    ->setOrganization($this);
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('organizations', 'Unable to associate types.');
        }

        $this->resetTypes();

        return $success;
    }

    /**
     * @param OrganizationTypeQuery $query
     * @return bool
     */
    public function dissociateTypes(OrganizationTypeQuery $query): bool
    {
        $types = $query->all();

        if (empty($types)) {
            return true;
        }

        $currentAssociations = OrganizationTypeAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;
        foreach ($types as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                continue;
            }

            if (!$association->delete()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('organizations', 'Unable to dissociate types.');
        }

        $this->resetTypes();

        return $success;
    }
}
