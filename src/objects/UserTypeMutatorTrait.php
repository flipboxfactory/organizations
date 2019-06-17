<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserTypeMutatorTrait
{
    /**
     * @param int|null $id
     */
    abstract protected function internalSetTypeId(int $id = null);

    /**
     * @return int|null
     */
    abstract protected function internalGetTypeId();

    /**
     * @param UserType|null $type
     */
    abstract protected function internalSetType(UserType $type = null);

    /**
     * @return UserType|null
     */
    abstract protected function internalGetType();

    /**
     * @param $id
     * @return $this
     */
    public function setTypeId(int $id = null)
    {
        $this->internalSetTypeId($id);

        if (null !== $this->internalGetType() && $id !== $this->internalGetType()->id) {
            $this->internalSetType(null);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTypeId()
    {
        if (null === $this->internalGetTypeId() && null !== $this->internalGetType()) {
            $this->setTypeId($this->internalGetType()->id);
        }

        return $this->internalGetTypeId();
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->internalSetType(null);
        $this->internalSetTypeId(null);

        if ($type = $this->internalResolveType($type)) {
            $this->internalSetType($type);
            $this->internalSetTypeId($type->id);
        }

        return $this;
    }

    /**
     * @return UserType|null
     */
    public function getType()
    {
        if ($this->internalGetType() === null) {
            $type = $this->resolveType();
            $this->setType($type);
            return $type;
        }

        $typeId = $this->internalGetTypeId();
        if ($typeId !== null && $typeId !== $this->internalGetType()->id) {
            $this->internalSetType(null);
            return $this->getType();
        }

        return $this->internalGetType();
    }

    /**
     * @return UserType|null
     */
    protected function resolveType()
    {
        if ($type = $this->resolveTypeFromId()) {
            return $type;
        }

        return null;
    }

    /**
     * @return UserType|null
     */
    private function resolveTypeFromId()
    {
        if (null === $this->internalGetTypeId()) {
            return null;
        }

        return UserType::findOne($this->internalGetTypeId());
    }

    /**
     * @param $type
     * @return UserType|null
     */
    protected function internalResolveType($type = null)
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof UserType) {
            return $type;
        }

        return UserType::findOne($type);
    }
}
