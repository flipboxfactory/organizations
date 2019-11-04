<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\events;

use Craft;
use craft\events\ConfigEvent;
use flipbox\organizations\records\UserType;
use yii\db\AfterSaveEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class ManageUserTypeProjectConfig
{
    /**
     * @param AfterSaveEvent $event
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public static function save(AfterSaveEvent $event)
    {
        /** @var UserType $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->set(
            'plugins.organizations.userTypes.' . $record->uid,
            $record->toProjectConfig()
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public static function delete(ConfigEvent $event)
    {
        /** @var UserType $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->remove(
            'plugins.organizations.userTypes.' . $record->uid
        );
    }
}
