<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\RegisterElementSourcesEvent;
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterUserElementSources
{
    /**
     * @param RegisterElementSourcesEvent $event
     */
    public static function handle(RegisterElementSourcesEvent $event)
    {
        if ($event->context === 'index') {
            $event->sources[] = [
                'heading' => "Organization Groups"
            ];

            $types = UserType::findAll([]);
            foreach ($types as $type) {
                $event->sources[] = [
                    'key' => 'type:' . $type->id,
                    'label' => \Craft::t('organizations', $type->name),
                    'criteria' => ['organization' => ['userType' => $type->id]],
                    'hasThumbs' => true
                ];
            }
        }
    }
}