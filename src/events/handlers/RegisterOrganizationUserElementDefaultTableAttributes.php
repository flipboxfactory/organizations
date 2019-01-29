<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\RegisterElementDefaultTableAttributesEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterOrganizationUserElementDefaultTableAttributes
{
    /**
     * @param RegisterElementDefaultTableAttributesEvent $event
     */
    public static function handle(RegisterElementDefaultTableAttributesEvent $event)
    {
        $event->tableAttributes[] = 'state';
    }
}
