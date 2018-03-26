<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\types\traits;

use flipbox\organization\Organizations;
use flipbox\organization\records\Type;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Lookup
{
    /**
     * @inheritdoc
     * @return Type
     */
    protected function find($identifier)
    {
        return Organizations::getInstance()->getTypes()->find($identifier);
    }
}
