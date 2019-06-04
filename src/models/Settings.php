<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use craft\base\Model;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\ember\models\FieldLayoutAttributeTrait;
use flipbox\craft\ember\validators\ModelValidator;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    use FieldLayoutAttributeTrait,
        SiteSettingAttributeTrait;

    /**
     * @var int|null|false
     */
    public $userSidebarTemplate = 'organizations/_components/hooks/users/details';

    /**
     * @var int
     */
    private $usersTabOrder = 10;

    /**
     * @var string
     */
    private $usersTabLabel = 'Users';

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
     * @return array
     */
    public function getUserStates(): array
    {
        return [
            'active' => Organizations::t('Active'),
            'pending' => Organizations::t('Pending'),
            'inactive' => Organizations::t('InActive')
        ];
    }

    /**
     * Get the User tab order found on the Organization entry page.
     */
    public function getUsersTabOrder(): int
    {
        return $this->usersTabOrder;
    }

    /**
     * Set the User tab order found on the Organization entry page.
     *
     * @param int $order
     * @return $this
     */
    public function setUsersTabOrder(int $order)
    {
        $this->usersTabOrder = $order;
        return $this;
    }

    /**
     * Get the User tab label found on the Organization entry page.
     */
    public function getUsersTabLabel(): string
    {
        return $this->usersTabLabel;
    }

    /**
     * Set the User tab label found on the Organization entry page.
     *
     * @param string $label
     * @return $this
     */
    public function setUsersTabLabel(string $label)
    {
        $this->usersTabLabel = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultUserState(): string
    {
        return 'active';
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
                        'usersTabOrder'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'siteSettings',
                        'usersTabOrder',
                        'usersTabLabel'
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
                'siteSettings',
                'usersTabOrder',
                'usersTabLabel',

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
                'siteSettings' => Organizations::t('Site Settings'),
                'usersTabOrder' => Organizations::t('User\'s Tab Order'),
                'usersTabLabel' => Organizations::t('User\'s Tab Label')
            ]
        );
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
