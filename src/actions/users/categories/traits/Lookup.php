<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\users\categories\traits;

use flipbox\organization\Organization;
use flipbox\organization\records\UserCategory;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Lookup
{
    /**
     * @inheritdoc
     * @return UserCategory
     */
    protected function find($identifier)
    {
        return Organization::getInstance()->getUserCategories()->find($identifier);
    }
}
