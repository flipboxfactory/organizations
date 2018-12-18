<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\RegisterElementTableAttributesEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterUserTableAttributes
{
    /**
     * @param RegisterElementTableAttributesEvent $event
     */
    public static function handle(RegisterElementTableAttributesEvent $event)
    {
        $event->tableAttributes['organizations'] = [
            'label' => \Craft::t('organizations', 'Organizations')
        ];
    }
}