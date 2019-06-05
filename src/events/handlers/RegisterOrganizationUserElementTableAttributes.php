<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use Craft;
use craft\events\RegisterElementTableAttributesEvent;
use craft\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterOrganizationUserElementTableAttributes
{
    /**
     * @param RegisterElementTableAttributesEvent $event
     */
    public static function handle(RegisterElementTableAttributesEvent $event)
    {
        // Remove default full name
        ArrayHelper::remove($event->tableAttributes, 'fullName');

        $event->tableAttributes['state'] = ['label' => Craft::t('organizations', 'State')];
        $event->tableAttributes['types'] = ['label' => Craft::t('organizations', 'User Types')];
        $event->tableAttributes['edit'] = ['label' => ''];
    }
}
