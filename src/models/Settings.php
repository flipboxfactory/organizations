<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use craft\base\Model;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\ember\models\FieldLayoutAttributeTrait;
use flipbox\craft\ember\validators\ModelValidator;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;

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
     * @var string
     */
    private $defaultUserState = UserAssociation::STATE_ACTIVE;

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
            UserAssociation::STATE_ACTIVE => Organizations::t('Active'),
            UserAssociation::STATE_PENDING => Organizations::t('Pending'),
            UserAssociation::STATE_INACTIVE => Organizations::t('Inactive')
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
     * Get the default user state to be applied to a user/organization association
     *
     * @return string
     */
    public function getDefaultUserState(): string
    {
        return $this->defaultUserState;
    }

    /**
     * Set the default user state to be applied to a user/organization association
     *
     * @param string $state
     * @return $this
     */
    public function setDefaultUserState(string $state)
    {
        $this->defaultUserState = $state;
        return $this;
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
                        'usersTabLabel',
                        'defaultUserState'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
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
                'defaultUserState'

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
                'usersTabLabel' => Organizations::t('User\'s Tab Label'),
                'defaultUserState' => Organizations::t('Default User State')
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
