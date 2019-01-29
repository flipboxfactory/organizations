<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use Craft;
use craft\events\RegisterElementActionsEvent;
use flipbox\organizations\elements\actions\DissociateUsersFromOrganizationAction;
use flipbox\organizations\elements\actions\EditUserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterOrganizationUserElementActions
{
    /**
     * @param RegisterElementActionsEvent $event
     */
    public static function handle(RegisterElementActionsEvent $event)
    {
        $event->actions = [
            [
                'type' => DissociateUsersFromOrganizationAction::class,
                'organization' => Craft::$app->getRequest()->getParam('organization')
            ],
            [
                'type' => EditUserAssociation::class,
                'organization' => Craft::$app->getRequest()->getParam('organization')
            ],
        ];
    }
}
