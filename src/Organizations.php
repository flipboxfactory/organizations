<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout as FieldLayoutModel;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\models\Settings as OrganizationSettings;
use flipbox\organizations\records\OrganizationType as OrganizationType;
use flipbox\organizations\web\twig\variables\Organization as OrganizationVariable;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationSettings getSettings()
 */
class Organizations extends BasePlugin
{
    use LoggerTrait;

    /**
     * The plugin category (used for logging)
     *
     * @var string
     */
    public static $category = 'organizations';

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sub-Modules
        $this->setModules([
            'cp' => cp\Cp::class
        ]);

        parent::init();

        // Fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            [
                events\handlers\RegisterFieldTypes::class,
                'handle'
            ]
        );

        // Elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            [
                events\handlers\RegisterElements::class,
                'handle'
            ]
        );

        // User Query Behavior(s)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_DEFINE_BEHAVIORS,
            [
                events\handlers\AttachUserQueryBehaviors::class,
                'handle'
            ]
        );

        // User Query (prepare)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_BEFORE_PREPARE,
            [
                events\handlers\PrepareUserQuery::class,
                'handle'
            ]
        );

        // User Behavior(s)
        Event::on(
            User::class,
            User::EVENT_DEFINE_BEHAVIORS,
            [
                events\handlers\AttachUserBehaviors::class,
                'handle'
            ]
        );

        // User Type sources
        Event::on(
            User::class,
            User::EVENT_REGISTER_SOURCES,
            [
                events\handlers\RegisterUserElementSources::class,
                'handle'
            ]
        );

        // Register attributes available on User index view
        Event::on(
            User::class,
            User::EVENT_REGISTER_TABLE_ATTRIBUTES,
            [
                events\handlers\RegisterUserTableAttributes::class,
                'handle'
            ]
        );

        // Set attributes on User index
        Event::on(
            User::class,
            User::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            [
                events\handlers\SetUserTableAttributeHtml::class,
                'handle'
            ]
        );

        // CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );

        // Twig variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('organizations', OrganizationVariable::class);
            }
        );

        // Show organization in the sidebar?
        $userSidebarTemplate = $this->getSettings()->userSidebarTemplate;
        if (!empty($userSidebarTemplate)) {
            Craft::$app->getView()->hook('cp.users.edit.details', function (&$context) use ($userSidebarTemplate) {
                return Craft::$app->getView()->renderTemplate(
                    $userSidebarTemplate,
                    ['context' => $context]
                );
            });
        }
    }

    /*******************************************
     * NAV
     *******************************************/

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        return array_merge(
            parent::getCpNavItem(),
            [
                'subnav' => [
                    'organizations.organizations' => [
                        'label' => Craft::t('organizations', 'Organizations'),
                        'url' => 'organizations'
                    ],
                    'organizations.general' => [
                        'label' => Craft::t('organizations', 'Settings'),
                        'url' => 'organizations/settings'
                    ],
                    'organizations.organization-types' => [
                        'label' => Craft::t('organizations', 'Organization Types'),
                        'url' => 'organizations/settings/organization-types',
                    ],
                    'organizations.user-types' => [
                        'label' => Craft::t('organizations', 'User Types'),
                        'url' => 'organizations/settings/user-types',
                    ]
                ]
            ]
        );
    }

    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $event->rules = array_merge(
            $event->rules,
            [
                // SETTINGS
                'organizations/settings' => 'organizations/cp/settings/view/general/index',

                // SETTINGS: USER TYPES
                'organizations/settings/user-types' => 'organizations/cp/settings/view/user-types/index',
                'organizations/settings/user-types/new' =>
                    'organizations/cp/settings/view/user-types/upsert',
                'organizations/settings/user-types/<identifier:\d+>' =>
                    'organizations/cp/settings/view/user-types/upsert',

                // SETTINGS: ORGANIZATION TYPES
                'organizations/settings/organization-types' =>
                    'organizations/cp/settings/view/organization-types/index',
                'organizations/settings/organization-types/new' =>
                    'organizations/cp/settings/view/organization-types/upsert',
                'organizations/settings/organization-types/<identifier:\d+>' =>
                    'organizations/cp/settings/view/organization-types/upsert',


                // ORGANIZATION
                'organizations' => 'organizations/cp/view/organizations/index',
                'organizations/new/<typeIdentifier:{handle}>' => 'organizations/cp/view/organizations/upsert',
                'organizations/new' => 'organizations/cp/view/organizations/upsert',
                'organizations/<identifier:\d+>' => 'organizations/cp/view/organizations/upsert',
                'organizations/<identifier:\d+>/<typeIdentifier:{handle}>' =>
                    'organizations/cp/view/organizations/upsert'

            ]
        );
    }


    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @inheritdoc
     * @return OrganizationSettings
     */
    protected function createSettingsModel()
    {
        return new OrganizationSettings();
    }

    /**
     * @inheritdoc
     * @return mixed|void|\yii\web\Response
     * @throws \yii\base\ExitException
     */
    public function getSettingsResponse()
    {

        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('organizations/settings')
        );

        Craft::$app->end();
    }


    /*******************************************
     * INSTALL / UNINSTALL
     *******************************************/

    /**
     * @throws \yii\base\Exception
     */
    public function afterInstall()
    {
        // CreateOrganization default field layout
        $fieldLayout = new FieldLayoutModel();
        $fieldLayout->type = self::class;

        // Delete existing layouts
        Craft::$app->getFields()->deleteLayoutsByType(self::class);
        Craft::$app->getFields()->deleteLayoutsByType(OrganizationType::class);
        Craft::$app->getFields()->deleteLayoutsByType(OrganizationElement::class);

        // Save layout
        Craft::$app->getFields()->saveLayout($fieldLayout);

        // Set settings array
        $settings = [
            'fieldLayoutId' => $fieldLayout->id
        ];

        Craft::$app->getPlugins()->savePluginSettings(
            $this,
            $settings
        );

        // Do parent
        parent::afterInstall();
    }

    /**
     * Remove all field layouts
     */
    public function afterUninstall()
    {
        Craft::$app->getFields()->deleteLayoutsByType(self::class);
        Craft::$app->getFields()->deleteLayoutsByType(OrganizationType::class);
        Craft::$app->getFields()->deleteLayoutsByType(OrganizationElement::class);

        // Do parent
        parent::afterUninstall();
    }

    /*******************************************
     * MODULES
     *******************************************/

    /**
     * @return cp\Cp
     */
    public function getCp()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getModule('cp');
    }
}
