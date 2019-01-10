<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\RegisterComponentTypesEvent;
use flipbox\organizations\fields\Organization;
use flipbox\organizations\fields\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterFieldTypes
{
    /**
     * @param RegisterComponentTypesEvent $event
     */
    public static function handle(RegisterComponentTypesEvent $event)
    {
        $event->types[] = Organization::class;
        $event->types[] = OrganizationType::class;
    }
}
