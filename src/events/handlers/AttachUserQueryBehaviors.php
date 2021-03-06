<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\DefineBehaviorsEvent;
use flipbox\organizations\queries\OrganizationAttributesToUserQueryBehavior;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AttachUserQueryBehaviors
{
    /**
     * @param DefineBehaviorsEvent $event
     */
    public static function handle(DefineBehaviorsEvent $event)
    {
        $event->behaviors['organization'] = OrganizationAttributesToUserQueryBehavior::class;
    }
}
