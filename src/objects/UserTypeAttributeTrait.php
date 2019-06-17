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
trait UserTypeAttributeTrait
{
    use UserTypeMutatorTrait;

    /**
     * @var UserType|null
     */
    private $type;

    /**
     * @var int|null
     */
    private $typeId;

    /**
     * @param int|null $id
     */
    protected function internalSetTypeId(int $id = null)
    {
        $this->typeId = $id;
    }

    /**
     * @return int|null
     */
    protected function internalGetTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param UserType|null $type
     */
    protected function internalSetType(UserType $type = null)
    {
        $this->type = $type;
    }

    /**
     * @return UserType|null
     */
    protected function internalGetType()
    {
        return $this->type;
    }
}
