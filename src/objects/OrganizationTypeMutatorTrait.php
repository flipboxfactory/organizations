<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use flipbox\organizations\records\OrganizationType;

/**
 * @property int|null $typeId
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationTypeMutatorTrait
{
    /**
     * @var OrganizationType|null
     */
    private $type;

    /**
     * Set associated typeId
     *
     * @param $id
     * @return $this
     */
    public function setTypeId(int $id)
    {
        $this->typeId = $id;
        return $this;
    }

    /**
     * Get associated typeId
     *
     * @return int|null
     */
    public function getTypeId()
    {
        if (null === $this->typeId && null !== $this->type) {
            $this->typeId = $this->type->id;
        }

        return $this->typeId;
    }

    /**
     * @param $type
     * @return static
     */
    public function setType($type = null)
    {
        $this->type = null;

        if (!$type = $this->internalResolveType($type)) {
            $this->type = $this->typeId = null;
        } else {
            $this->typeId = $type->id;
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @return OrganizationType|null
     */
    public function getType()
    {
        if ($this->type === null) {
            $type = $this->resolveType();
            $this->setType($type);
            return $type;
        }

        $typeId = $this->typeId;
        if ($typeId !== null &&
            $typeId !== $this->type->id
        ) {
            $this->type = null;
            return $this->getType();
        }

        return $this->type;
    }

    /**
     * @return OrganizationType|null
     */
    protected function resolveType()
    {
        if ($type = $this->resolveTypeFromId()) {
            return $type;
        }

        return null;
    }

    /**
     * @return OrganizationType|null
     */
    private function resolveTypeFromId()
    {
        if (null === $this->typeId) {
            return null;
        }

        return OrganizationType::findOne($this->typeId);
    }

    /**
     * @param $type
     * @return OrganizationType|null
     */
    protected function internalResolveType($type = null)
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof OrganizationType) {
            return $type;
        }

        return OrganizationType::findOne($type);
    }
}
