<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use Craft;
use craft\base\Model;
use flipbox\craft\ember\models\FieldLayoutAttributeTrait;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\ember\validators\ModelValidator;
use flipbox\organizations\elements\Organization;
use yii\caching\Dependency;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    use FieldLayoutAttributeTrait,
        traits\SiteSettingAttribute;

    /**
     * @var int|null|false
     */
    public $userSidebarTemplate = 'organizations/_components/hooks/users/details';

    /**
     * @var int|null|false
     */
    public $organizationTypeAssociationsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $organizationTypeAssociationsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $organizationTypeSettingsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $organizationTypeSettingsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $organizationUserAssociationsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $organizationUserAssociationsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $usersCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $usersCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $userOrganizationAssociationsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $userOrganizationAssociationsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $userTypeAssociationsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $userTypeAssociationsCacheDependency = null;

    /**
     * @return string
     */
    public static function siteSettingsClass(): string
    {
        return SiteSettings::class;
    }

    /**
     * @return string
     */
    protected static function fieldLayoutType(): string
    {
        return Organization::class;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->fieldLayoutRules(),
            [
                [
                    [
                        'siteSettings'
                    ],
                    ModelValidator::class
                ],
                [
                    [
                        'siteSettings'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            $this->fieldLayoutAttributes(),
            [
                'siteSettings'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->fieldLayoutAttributeLabels(),
            [
                'siteSettings' => Craft::t('organizations', 'Site Settings')
            ]
        );
    }


    /*******************************************
     * STATES
     *******************************************/

    /**
     * @return $this
     * @deprecated
     */
    public function setStates()
    {
        return $this;
    }

    /**
     * @return $this
     * @deprecated
     */
    public function setDefaultStates()
    {
        return $this;
    }


    /*******************************************
     * SITE SETTINGS
     *******************************************/

    /**
     * @param int|null $siteId
     * @return bool
     * @throws \craft\errors\SiteNotFoundException
     */
    public function isSiteEnabled(int $siteId = null): bool
    {
        $siteSettings = $this->getSiteSettings();
        return array_key_exists(
            SiteHelper::ensureSiteId($siteId),
            $siteSettings
        );
    }

    /**
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getEnabledSiteIds(): array
    {
        return array_keys($this->getSiteSettings());
    }
}
