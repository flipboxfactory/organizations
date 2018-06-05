<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\queue\jobs\ResaveElements;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\records\OrganizationType as TypeModel;
use flipbox\organizations\records\OrganizationTypeSiteSettings as TypeSettingsRecord;
use yii\base\Component;
use flipbox\organizations\Organizations as OrganizationPlugin;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method TypeSettingsRecord create(array $attributes = [])
 * @method TypeSettingsRecord find($identifier)
 * @method TypeSettingsRecord get($identifier)
 * @method TypeSettingsRecord findByCondition($condition = [])
 * @method TypeSettingsRecord getByCondition($condition = [])
 * @method TypeSettingsRecord findByCriteria($criteria = [])
 * @method TypeSettingsRecord getByCriteria($criteria = [])
 * @method TypeSettingsRecord[] findAllByCondition($condition = [])
 * @method TypeSettingsRecord[] getAllByCondition($condition = [])
 * @method TypeSettingsRecord[] findAllByCriteria($criteria = [])
 * @method TypeSettingsRecord[] getAllByCriteria($criteria = [])
 */
class OrganizationTypeSettings extends Component
{
    use Accessor;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->organizationTypeSettingsCacheDuration;
        $this->cacheDependency = $settings->organizationTypeSettingsCacheDependency;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return TypeSettingsRecord::class;
    }

    /*******************************************
     * SAVE ALL BY TYPE
     *******************************************/

    /**
     * @param TypeModel $type
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveByType(
        TypeModel $type
    ): bool {
        $successful = true;

        /** @var TypeSettingsRecord[] $allSettings */
        $allSettings = $type->hasMany(TypeSettingsRecord::class, ['typeId' => 'id'])
            ->indexBy('siteId')
            ->all();

        foreach ($type->getSiteSettings() as $model) {
            ArrayHelper::remove($allSettings, $model->siteId);
            $model->typeId = $type->getId();

            if (!$model->save()) {
                $successful = false;
                // Log the errors
                $error = Craft::t(
                    'organizations',
                    "Couldn't save site settings due to validation errors:"
                );
                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('organizations', $attributeError);
                }

                $type->addError('sites', $error);
            }
        }

        // Delete old settings records
        foreach ($allSettings as $settings) {
            $settings->delete();
            $this->reSaveOrganizations($settings);
        }

        return $successful;
    }

    /**
     * @param TypeSettingsRecord $type
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave(TypeSettingsRecord $type, bool $insert, array $changedAttributes)
    {
        if ($insert === true) {
            return;
        }

        if (array_key_exists('uriFormat', $changedAttributes) ||
            (array_key_exists('hasUrls', $changedAttributes) &&
                $type->getOldAttribute('hasUrls') != $type->getAttribute('hasUrls'))
        ) {
            $this->reSaveOrganizations($type);
        }
    }

    /**
     * @param TypeSettingsRecord $type
     */
    private function reSaveOrganizations(TypeSettingsRecord $type)
    {
        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => Craft::t('organizations', 'Re-saving organizations (Site: {site})', [
                'site' => $type->getSiteId(),
            ]),
            'elementType' => OrganizationElement::class,
            'criteria' => [
                'siteId' => $type->getSiteId(),
                'typeId' => $type->getTypeId(),
                'status' => null,
                'enabledForSite' => false,
            ]
        ]));
    }
}
