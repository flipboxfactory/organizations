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
use flipbox\organizations\managers\OrganizationTypeAssociationManager;
use flipbox\organizations\queries\OrganizationTypeQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationType as TypeModel;

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
            $this->getTypeManager()->setMany((array)$types);
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
            $this->getTypeManager()->addOne($type);
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
     * @return OrganizationType[]
     */
    public function getTypes(): array
    {
        return ArrayHelper::getColumn(
            $this->getTypeManager()->findAll(),
            'type'
        );
    }

    /**
     * Associate types to an organization
     *
     * @param $types
     * @return $this
     *
     * @deprecated
     */
    public function setTypes($types)
    {
        $this->getTypeManager()->setMany($types);
        return $this;
    }

    /**
     * Associate an array of types to an organization
     *
     * @param $types
     * @return $this
     *
     * @deprecated
     */
    public function addTypes(array $types)
    {
        $this->getTypeManager()->addMany($types);
        return $this;
    }

    /**
     * Associate a type to an organization
     *
     * @param OrganizationType $type
     * @return $this
     *
     * @deprecated
     */
    public function addType(OrganizationType $type)
    {
        $this->getTypeManager()->addOne($type);
        return $this;
    }

    /**
     * Dissociate an array of types from an organization
     *
     * @param array $types
     * @return $this
     *
     * @deprecated
     */
    public function removeTypes(array $types)
    {
        $this->getTypeManager()->removeMany($types);
        return $this;
    }

    /**
     * Dissociate a type from an organization
     *
     * @param OrganizationType $type
     * @return $this
     *
     * @deprecated
     */
    public function removeType(OrganizationType $type)
    {
        $this->getTypeManager()->removeOne($type);
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated
     */
    public function resetTypes()
    {
        $this->getTypeManager()->reset();
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
            $this->getTypes(),
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
        return count($this->getTypes()) > 0;
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
                $this->getTypes()
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

        $types = $this->getTypes();

        return reset($types);
    }

    /**
     * @return bool
     * @throws \Throwable
     *
     * @deprecated
     */
    public function saveTypes(): bool
    {
        return $this->getTypeManager()->save();
    }

    /**
     * @param OrganizationType $type
     * @param int|null $sortOrder
     * @return bool
     *
     * @deprecated
     */
    public function associateType(OrganizationType $type, int $sortOrder = null): bool
    {
        return $this->getTypeManager()->associateOne($type, ['sortOrder' => $sortOrder]);
    }

    /**
     * @param OrganizationTypeQuery|OrganizationType[] $types
     * @return bool
     * @return bool
     * @throws \Throwable
     *
     * @deprecated
     */
    public function associateTypes($types): bool
    {
        return $this->getTypeManager()->associateMany($types);
    }

    /**
     * @param OrganizationType $type
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @deprecated
     */
    public function dissociateType(OrganizationType $type): bool
    {
        return $this->getTypeManager()->dissociateOne($type);
    }

    /**
     * @param OrganizationTypeQuery|OrganizationType[] $types
     * @return bool
     *
     * @deprecated
     */
    public function dissociateTypes($types): bool
    {
        return $this->getTypeManager()->dissociateMany($types);
    }
}
