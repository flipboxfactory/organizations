<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\actions\general;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use flipbox\ember\actions\model\ModelCreate;
use flipbox\ember\exceptions\ModelNotFoundException;
use flipbox\organization\models\Settings;
use flipbox\organization\models\SiteSettings;
use flipbox\organization\Organizations;
use yii\base\BaseObject;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method array parentNormalizeSiteConfig($config = [])
 */
class Update extends ModelCreate
{
    use traits\SiteSettingAttributes {
        normalizeSiteConfig as parentNormalizeSiteConfig;
    }

    /**
     * @param BaseObject $settings
     * @return bool
     * @throws ModelNotFoundException
     */
    protected function ensureSettings(BaseObject $settings): bool
    {
        if (!$settings instanceof Settings) {
            throw new ModelNotFoundException(sprintf(
                "Settings must be an instance of '%s', '%s' given.",
                Settings::class,
                get_class($settings)
            ));
        }

        return true;
    }

    /**
     * These are the default body params that we're accepting.  You can lock down specific Client attributes this way.
     *
     * @return array
     */
    protected function validBodyParams(): array
    {
        return [
            'requireOwner',
            'uniqueOwner'
        ];
    }

    /**
     * @inheritdoc
     */
    public function statusCodeSuccess(): int
    {
        return 200;
    }

    /**
     * @inheritdoc
     */
    protected function attributeValuesFromBody(): array
    {
        $attributes = parent::attributeValuesFromBody();
        $attributes['states'] = $this->stateValuesFromBody();
        return $attributes;
    }

    /**
     * Normalize settings from body
     *
     * @return array|null
     */
    protected function stateValuesFromBody()
    {
        if ($rawStatuses = Craft::$app->getRequest()->getBodyParam('states', [])) {
            $stateArray = [];

            foreach (ArrayHelper::toArray($rawStatuses) as $rawStatus) {
                $stateArray = array_merge(
                    $stateArray,
                    $this->normalizeStateConfig($rawStatus)
                );
            }
        }
        return $stateArray ?? null;
    }

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
     * @param string|array $config
     * @return array
     */
    protected function normalizeStateConfig($config = []): array
    {
        if (!is_array($config)) {
            $config = [
                'label' => StringHelper::toTitleCase((string)$config),
                'value' => $config
            ];
        }

        return [ArrayHelper::getValue($config, 'value') => ArrayHelper::getValue($config, 'label')];
    }

    /**
     * @inheritdoc
     * @param Settings $model
     */
    protected function performAction(Model $model): bool
    {
        return Organizations::getInstance()->getCp()->getSettings()->save($model);
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
     * @param Settings $object
     * @return Settings
     */
    protected function populate(BaseObject $object): BaseObject
    {
        if (true === $this->ensureSettings($object)) {
            parent::populate($object);
            $this->populateSiteSettings($object);
            $this->populateSiteLayout($object);
        }

        return $object;
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
