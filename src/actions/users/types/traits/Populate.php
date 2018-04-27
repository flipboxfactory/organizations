<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users\types\traits;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Populate
{
    /**
     * These are the default body params that we're accepting.  You can lock down specific Client attributes this way.
     *
     * @return array
     */
    protected function validBodyParams(): array
    {
        return [
            'name',
            'handle'
        ];
    }
}
