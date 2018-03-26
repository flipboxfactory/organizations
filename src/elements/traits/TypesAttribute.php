<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\elements\traits;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organization\db\TypeQuery;
use flipbox\organization\Organization as OrganizationPlugin;
use flipbox\organization\records\Type;
use flipbox\organization\records\Type as TypeModel;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TypesAttribute
{
    /**
     * @var TypeQuery
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
     * Associate an array of types from request input
     *
     * @param string $identifier
     * @return $this
     */
    public function setTypesFromRequest(string $identifier = 'types')
    {
        if ($types = Craft::$app->getRequest()->getBodyParam($identifier, [])) {
            $this->setTypes($types);
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
     * @return TypeQuery
     */
    public function getTypes($criteria = [])
    {
        if (null === $this->types) {
            $this->types = OrganizationPlugin::getInstance()->getTypes()->getQuery([
                'organization' => $this
            ]);
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
     * Associate types to an organization
     *
     * @param $types
     * @return $this
     */
    public function setTypes($types)
    {
        if ($types instanceof TypeQuery) {
            $this->types = $types;
            return $this;
        }

        // Reset the query
        $this->types = OrganizationPlugin::getInstance()->getTypes()->getQuery([
            'organization' => $this
        ]);

        // Remove all types
        $this->types->setCachedResult([]);

        return $this->addTypes($types);
    }

    /**
     * Associate an array of types to an organization
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
            if (!$type instanceof Type) {
                $type = OrganizationPlugin::getInstance()->getTypes()->resolve($type);
            }

            $this->addType($type);
        }

        return $this;
    }

    /**
     * Associate a type to an organization
     *
     * @param Type $type
     * @return $this
     */
    public function addType(Type $type)
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
     * Dissociate a type from an organization
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
            if (!$type instanceof Type) {
                $type = OrganizationPlugin::getInstance()->getTypes()->resolve($type);
            }

            $this->removeType($type);
        }

        return $this;
    }

    /**
     * Dissociate a type from an organization
     *
     * @param Type $type
     * @return $this
     */
    public function removeType(Type $type)
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
}
