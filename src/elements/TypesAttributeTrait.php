<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements;

use Craft;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\managers\OrganizationTypeAssociationManager;
use flipbox\organizations\queries\OrganizationTypeQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;
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
     * @var OrganizationTypeAssociationManager
     */
    private $typeManager;

    /**
     * @return OrganizationTypeAssociationManager
     */
    public function getTypeManager(): OrganizationTypeAssociationManager
    {
        if (null === $this->typeManager) {
            $this->typeManager = new OrganizationTypeAssociationManager($this);
        }

        return $this->typeManager;
    }

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
            $this->getTypeManager()->setMany((array)$types);
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
            $this->getTypeManager()->addOne($type);
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
     * TYPES QUERY
     ************************************************************/

    /**
     * @param array $criteria
     * @return OrganizationTypeQuery
     */
    public function userTypeQuery($criteria = []): OrganizationTypeQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = OrganizationType::find()
            ->organization($this);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /************************************************************
     * TYPES
     ************************************************************/

    /**
     * Get an array of types associated to an organization
     *
     * @return OrganizationType[]|Collection
     */
    public function getTypes(): Collection
    {
        return $this->getTypeManager()->findAll()
            ->filter(function (OrganizationTypeAssociation $association) {
                return null !== $association->getType();
            })
            ->pluck('type')->filter();
    }

    /**
     * Set an array or query of users to an organization
     *
     * @param $types
     * @return $this
     */
    public function setTypes($types)
    {
        $this->getTypeManager()->setMany($types);
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
        return $this->getTypes()->isNotEmpty();
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

        $this->getTypeManager()->setMany(
            array_merge(
                [
                    $type
                ],
                $this->getTypes()
            )
        );

        return $this;
    }

    /**
     * Get the primary type
     *
     * @return OrganizationType|null
     */
    public function getPrimaryType()
    {
        return $this->getTypes()->first();
    }
}
