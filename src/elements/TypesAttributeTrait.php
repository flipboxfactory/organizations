<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements;

use Craft;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\relationships\RelationshipInterface;
use flipbox\organizations\relationships\OrganizationTypeRelationship;
use flipbox\organizations\queries\OrganizationTypeQuery;
use flipbox\organizations\records\OrganizationType;
use Tightenco\Collect\Support\Collection;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @mixin Organization
 */
trait TypesAttributeTrait
{
    /**
     * @var RelationshipInterface
     */
    private $typeManager;

    /**
     * @var OrganizationType|false
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
            $this->getTypes()->clear()->add($types);
        }

        return $this;
    }

    /************************************************************
     * ACTIVE TYPE
     ************************************************************/

    /**
     * @param OrganizationType|null $type
     * @return $this
     */
    public function setActiveType(OrganizationType $type = null)
    {
        if ($type) {
            $this->getTypes()->add($type);
        }

        $this->activeType = (null === $type) ? false : $type;
        return $this;
    }

    /**
     * @return OrganizationType|null
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
     * @return OrganizationTypeRelationship|RelationshipInterface
     */
    public function getTypes(): RelationshipInterface
    {
        if (null === $this->typeManager) {
            $this->typeManager = new OrganizationTypeRelationship($this);
        }

        return $this->typeManager;
    }

    /**
     * Set an array or query of users to an organization
     *
     * @param $types
     * @return $this
     */
    public function setTypes($types)
    {
        $this->getTypes()->clear()->add($types);
        return $this;
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
        return $this->getTypes()->getCollection()->isNotEmpty();
    }

    /**
     * Identify whether the type is primary
     *
     * @param $type
     * @return bool
     */
    public function isPrimaryType(OrganizationType $type)
    {
        if ($primaryType = $this->getPrimaryType()) {
            return $primaryType->id === $type->id;
        }

        return false;
    }

    /**
     * @param OrganizationType $type
     * @return $this
     */
    public function setPrimaryType(OrganizationType $type = null)
    {
        if (null === $type) {
            return $this;
        }

        $this->getTypes()->getCollection()->prepend($type);

        return $this;
    }

    /**
     * Get the primary type
     *
     * @return OrganizationType|null
     */
    public function getPrimaryType()
    {
        return $this->getTypes()->getCollection()->first();
    }
}
