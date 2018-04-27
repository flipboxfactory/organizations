<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\types\traits;

use flipbox\organizations\Organizations;
use flipbox\organizations\records\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Lookup
{
    /**
     * @inheritdoc
     * @return OrganizationType
     */
    protected function find($identifier)
    {
        return Organizations::getInstance()->getTypes()->find($identifier);
    }
}
