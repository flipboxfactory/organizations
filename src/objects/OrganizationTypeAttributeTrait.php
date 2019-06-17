<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use flipbox\organizations\records\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationTypeAttributeTrait
{
    use OrganizationTypeMutatorTrait;

    /**
     * @var OrganizationType|null
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
     * @param OrganizationType|null $type
     */
    protected function internalSetType(OrganizationType $type = null)
    {
        $this->type = $type;
    }

    /**
     * @return OrganizationType|null
     */
    protected function internalGetType()
    {
        return $this->type;
    }
}
