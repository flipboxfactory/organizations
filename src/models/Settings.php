<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use Craft;
use craft\base\Model;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\SiteHelper;
use flipbox\ember\traits\FieldLayoutAttribute;
use flipbox\ember\validators\ModelValidator;
use flipbox\organizations\elements\Organization;
use yii\caching\Dependency;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    use FieldLayoutAttribute,
        traits\SiteSettingAttribute;

    /**
     * @var array|null
     */
    private $states;

    /**
     * @var string
     */
    public $defaultState;

    /**
     * @var int|null|false
     */
    public $recordsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $recordsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $organizationsCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $organizationsCacheDependency = null;

    /**
     * @var int|null|false
     */
    public $organizationTypesCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $organizationTypesCacheDependency = null;

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
    public $userTypesCacheDuration = false;

    /**
     * @var null|Dependency
     */
    public $userTypesCacheDependency = null;

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
                        'siteSettings',
                        'states'
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
                'states',
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
                'states' => Craft::t('organizations', 'States'),
                'siteSettings' => Craft::t('organizations', 'Site Settings')
            ]
        );
    }


    /*******************************************
     * STATES
     *******************************************/

    /**
     * @return bool
     */
    public function hasStates(): bool
    {
        return !empty($this->states);
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return (array)$this->states;
    }

    /**
     * @param null|array|string $states
     * @return $this
     */
    public function setStates($states = null)
    {
        if ($states === null) {
            $this->states = null;
            return $this;
        }

        if (!is_array($states)) {
            $states = [$states];
        }

        $this->states = $states;
        return $this;
    }


    /*******************************************
     * SITE SETTINGS
     *******************************************/

    /**
     * @param int|null $siteId
     * @return bool
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
     */
    public function getEnabledSiteIds(): array
    {
        return array_keys($this->getSiteSettings());
    }
}
