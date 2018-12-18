<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\DefineBehaviorsEvent;
use flipbox\organizations\elements\behaviors\UserOrganizationsBehavior;
use flipbox\organizations\elements\behaviors\UserTypesBehavior;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AttachUserBehaviors
{
    /**
     * @param DefineBehaviorsEvent $event
     */
    public static function handle(DefineBehaviorsEvent $event)
    {
        $event->behaviors['organizations'] = UserOrganizationsBehavior::class;
        $event->behaviors['types'] = UserTypesBehavior::class;
    }
}