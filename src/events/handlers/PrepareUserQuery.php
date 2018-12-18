<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\elements\db\UserQuery;
use craft\events\CancelableEvent;
use flipbox\organizations\queries\OrganizationAttributesToUserQueryBehavior;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class PrepareUserQuery
{
    /**
     * @param CancelableEvent $event
     */
    public static function handle(CancelableEvent $event)
    {
        /** @var UserQuery $query */
        $query = $event->sender;

        /** @var OrganizationAttributesToUserQueryBehavior $behavior */
        if (null !== ($behavior = $query->getBehavior('organization'))) {
            $behavior->applyOrganizationParams($query);
        }
    }
}