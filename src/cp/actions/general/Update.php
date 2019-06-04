<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\actions\general;

use Craft;
use flipbox\craft\ember\actions\models\CreateModel;
use flipbox\organizations\cp\actions\general\traits\SiteSettingAttributesTrait;
use flipbox\organizations\models\Settings;
use flipbox\organizations\models\SiteSettings;
use flipbox\organizations\Organizations;
use yii\base\Model;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method array parentNormalizeSiteConfig($config = [])
 */
class Update extends CreateModel
{
    use SiteSettingAttributesTrait {
        normalizeSiteConfig as parentNormalizeSiteConfig;
    }

    /**
     * These are the default body params that we're accepting.  You can lock down specific Client attributes this way.
     *
     * @return array
     */
    public $validBodyParams = [
        'defaultUserState'
    ];

    /**
     * @inheritdoc
     */
    public $statusCodeSuccess = 200;

    /**
     * @param array $config
     * @return array
     */
    protected function normalizeSiteConfig($config = []): array
    {
        return array_merge(
            $this->parentNormalizeSiteConfig($config),
            [
                'enabledByDefault' => (bool)$config['enabledByDefault'] ?? false
            ]
        );
    }

    /**
     * @inheritdoc
     * @param Settings $model
     * @throws \Throwable
     */
    protected function performAction(Model $model): bool
    {
        $fieldLayout = $model->getFieldLayout();

        // Save field layout
        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            throw new HttpException(401, "Unable to save field layout");
        }

        return Craft::$app->getPlugins()->savePluginSettings(
            Organizations::getInstance(),
            $model->toArray()
        );
    }

    /**
     * @inheritdoc
     * @return Settings
     */
    protected function newModel(array $config = []): Model
    {
        return Organizations::getInstance()->getSettings();
    }


    /*******************************************
     * POPULATE
     *******************************************/

    /**
     * @inheritdoc
     * @param Settings $model
     * @return Settings
     */
    protected function populate(Model $model): Model
    {
        parent::populate($model);
        $this->populateSiteSettings($model);
        $this->populateSiteLayout($model);

        return $model;
    }

    /**
     * @param Settings $model
     * @return Settings
     */
    private function populateSiteLayout(Settings $model): Settings
    {
        if ($fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost()) {
            $model->setFieldLayout($fieldLayout);
        }

        return $model;
    }

    /**
     * @param Settings $model
     * @return Settings
     */
    private function populateSiteSettings(Settings $model): Settings
    {
        if (null !== ($sites = $this->sitesSettingsFromBody())) {
            $enabledSites = [];

            foreach ($sites as $siteId => $siteConfig) {
                if (!($siteConfig['enabled'] ?? false)) {
                    continue;
                }

                $siteConfig['siteId'] = $siteId;
                $siteConfig['class'] = SiteSettings::class;
                $enabledSites[$siteId] = $siteConfig;
            }

            $model->setSiteSettings($enabledSites);
        }
        return $model;
    }
}
