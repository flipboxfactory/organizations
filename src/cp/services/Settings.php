<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\services;

use Craft;
use flipbox\organizations\models\Settings as SettingsModel;
use flipbox\organizations\Organizations as OrganizationPlugin;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Component
{
    /**
     * @param SettingsModel $settingsModel
     * @return bool
     * @throws \Throwable
     */
    public function save(SettingsModel $settingsModel)
    {
        $fieldLayout = $settingsModel->getFieldLayout();

        // Save field layout
        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            throw new InvalidConfigException("Unable to save field layout");
        }

        return Craft::$app->getPlugins()->savePluginSettings(
            OrganizationPlugin::getInstance(),
            $settingsModel->toArray()
        );
    }
}
