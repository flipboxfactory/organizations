<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use craft\helpers\ArrayHelper;
use craft\models\FieldLayout;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\FieldLayoutAttributeTrait;
use flipbox\craft\ember\validators\ModelValidator;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationTypeQuery;
use yii\base\Exception;
use yii\db\ActiveQueryInterface;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $name
 * @property OrganizationTypeSiteSettings[] $siteSettingRecords
 */
class OrganizationType extends ActiveRecordWithId
{
    use FieldLayoutAttributeTrait,
        HandleRulesTrait {
        resolveFieldLayout as parentResolveFieldLayout;
    }

    /**
     * The table name
     */
    const TABLE_ALIAS = Organization::TABLE_ALIAS . '_types';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['fieldLayoutId'];

    /**
     * @inheritdoc
     */
    protected static function fieldLayoutType(): string
    {
        return self::class;
    }

    /**
     * @inheritdoc
     */
    protected function resolveFieldLayout()
    {
        if (null === ($fieldLayout = $this->parentResolveFieldLayout())) {
            $fieldLayout = OrganizationPlugin::getInstance()->getSettings()->getFieldLayout();
        }

        return $fieldLayout;
    }


    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @inheritdoc
     * @return OrganizationTypeQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(OrganizationTypeQuery::class, [get_called_class()]);
    }

    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function beforeSave($insert)
    {
        if (false === parent::beforeSave($insert)) {
            return false;
        }

        $fieldLayout = $this->getFieldLayout();

        $this->handleOldFieldLayout($fieldLayout);

        if ($fieldLayout->id == $this->getDefaultFieldLayoutId()) {
            return true;
        }

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            return false;
        }

        return true;
    }

    /**
     * @param FieldLayout $fieldLayout
     */
    protected function handleOldFieldLayout(FieldLayout $fieldLayout)
    {
        $oldFieldLayoutId = (int)$this->getOldAttribute('fieldLayoutId');

        if ($oldFieldLayoutId != $fieldLayout->id &&
            $oldFieldLayoutId != $this->getDefaultFieldLayoutId()
        ) {
            Craft::$app->getFields()->deleteLayoutById($oldFieldLayoutId);
        }
    }

    /**
     * @return int
     */
    protected function getDefaultFieldLayoutId(): int
    {
        return (int)OrganizationPlugin::getInstance()->getSettings()->getFieldLayout()->id;
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\db\StaleObjectException
     */
    public function afterSave($insert, $changedAttributes)
    {
        $successful = true;

        /** @var OrganizationTypeSiteSettings[] $allSettings */
        $allSettings = $this->hasMany(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
            ->indexBy('siteId')
            ->all();

        foreach ($this->getSiteSettings() as $model) {
            ArrayHelper::remove($allSettings, $model->siteId);
            $model->typeId = $this->getId();

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

                $this->addError('sites', $error);
            }
        }

        // DeleteOrganization old settings records
        foreach ($allSettings as $settings) {
            $settings->delete();
        }

        if (!$successful) {
            throw new Exception("Unable to save site settings");
        };

        parent::afterSave($insert, $changedAttributes);
    }



    /*******************************************
     * SITE SETTINGS
     *******************************************/

    /**
     * @return OrganizationTypeSiteSettings[]
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSiteSettings(): array
    {
        if (empty($this->siteSettingRecords)) {
            $this->addPrimarySiteSettings();
        }

        return $this->siteSettingRecords;
    }

    /**
     * @param array $siteSettings
     * @return $this
     */
    public function setSiteSettings(array $siteSettings = [])
    {
        foreach ($siteSettings as $siteId => &$site) {
            $site = $this->resolveSiteSettings($siteId, $site);
        }

        $this->populateRelation('siteSettingRecords', $siteSettings);
        return $this;
    }

    /**
     * @return $this
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function addPrimarySiteSettings()
    {
        $primarySite = Craft::$app->getSites()->getPrimarySite();

        if ($primarySite->id !== null) {
            $this->addSiteSettings($primarySite->id, ['site' => $primarySite]);
        }

        return $this;
    }

    /**
     * @param int $siteId
     * @param $site
     * @return OrganizationTypeSiteSettings
     */
    protected function resolveSiteSettings(int $siteId, $site): OrganizationTypeSiteSettings
    {
        if (!$record = $this->siteSettingRecords[$siteId] ?? null) {
            $record = new OrganizationTypeSiteSettings();
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ObjectHelper::populate(
            $record,
            $site
        );
    }

    /**
     * @param int $siteId
     * @param $site
     * @return $this
     */
    protected function addSiteSettings(int $siteId, $site)
    {
        $site = $this->resolveSiteSettings($siteId, $site);
        $this->populateRelation('siteSettingRecords', (
            $this->siteSettingRecords +
            [
                $site->getSiteId() => $site
            ]
        ));

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            $this->fieldLayoutRules(),
            [
                [
                    [
                        'name'
                    ],
                    'required'
                ],
                [
                    [
                        'siteSettings'
                    ],
                    ModelValidator::class
                ],
                [
                    [
                        'name',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'handle'
                    ],
                    UniqueValidator::class
                ]
            ]
        );
    }

    /**
     * @return ActiveQueryInterface
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function getSiteSettingRecords(): ActiveQueryInterface
    {
        return $this->hasMany(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
            ->where([
                'siteId' => OrganizationPlugin::getInstance()->getSettings()->getEnabledSiteIds()
            ])
            ->indexBy('siteId');
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->getAttribute('name');
    }
}
