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
use craft\events\CancelableEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementSourcesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout as FieldLayoutModel;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\organizations\db\behaviors\OrganizationAttributesToUserQueryBehavior;
use flipbox\organizations\elements\behaviors\UserCategoriesBehavior;
use flipbox\organizations\elements\behaviors\UserOrganizationsBehavior;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\fields\Organization as OrganizationField;
use flipbox\organizations\models\Settings as OrganizationSettings;
use flipbox\organizations\records\Type as OrganizationType;
use flipbox\organizations\web\twig\variables\Organization as OrganizationVariable;
use yii\base\Event;
use yii\log\Logger;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationSettings getSettings()
 */
class Organizations extends BasePlugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OrganizationField::class;
            }
        );

        // Elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OrganizationElement::class;
            }
        );

        // User Query (attach behavior)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_INIT,
            function (Event $e) {
                /** @var UserQuery $query */
                $query = $e->sender;
                $query->attachBehaviors([
                    'organization' => OrganizationAttributesToUserQueryBehavior::class
                ]);
            }
        );

        // User Query (prepare)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_AFTER_PREPARE,
            function (CancelableEvent $e) {
                /** @var UserQuery $query */
                $query = $e->sender;

                /** @var OrganizationAttributesToUserQueryBehavior $behavior */
                if (null !== ($behavior = $query->getBehavior('organization'))) {
                    $behavior->applyOrganizationParams($query);
                }
            }
        );

        // User Query (attach behavior)
        Event::on(
            User::class,
            User::EVENT_INIT,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;

                $user->attachBehaviors([
                    'organizations' => UserOrganizationsBehavior::class,
                    'categories' => UserCategoriesBehavior::class
                ]);
            }
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

        // User Category sources
        Event::on(
            User::class,
            User::EVENT_REGISTER_SOURCES,
            function (RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    $event->sources[] = [
                        'heading' => "Organization Groups"
                    ];

                    $userCategories = static::getInstance()->getUserCategories()->findAll();
                    foreach ($userCategories as $userCategory) {
                        $event->sources[] = [
                            'key' => 'category:' . $userCategory->id,
                            'label' => Craft::t('organizations', $userCategory->name),
                            'criteria' => ['organization' => ['userCategory' => $userCategory->id]],
                            'hasThumbs' => true
                        ];
                    }
                }
            }
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

                'organizations/settings/user-categories' => 'organizations/cp/settings/view/user-categories/index',
                'organizations/settings/user-categories/new' =>
                    'organizations/cp/settings/view/user-categories/upsert',
                'organizations/settings/user-categories/<identifier:\d+>' =>
                    'organizations/cp/settings/view/user-categories/upsert',

                'organizations/settings/types' => 'organizations/cp/settings/view/types/index',
                'organizations/settings/types/new' => 'organizations/cp/settings/view/types/upsert',
                'organizations/settings/types/<identifier:\d+>' => 'organizations/cp/settings/view/types/upsert',


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
     * Delete any existing field layouts, and create default settings
     */
    public function afterInstall()
    {
        // Create default field layout
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
     * @deprecated
     */
    public function getConfiguration()
    {
        return $this->getCp();
    }

    /**
     * @return cp\Cp
     */
    public function getCp()
    {
        return $this->getModule('cp');
    }


    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @return services\Element
     */
    public function getElement()
    {
        return $this->get('element');
    }

    /**
     * @return services\Organizations
     */
    public function getOrganizations()
    {
        return $this->get('organizations');
    }

    /**
     * @return services\Records
     */
    public function getRecords()
    {
        return $this->get('records');
    }

    /**
     * @return services\Types
     */
    public function getTypes()
    {
        return $this->get('types');
    }

    /**
     * @return services\TypeSettings
     */
    public function getTypeSettings()
    {
        return $this->get('typeSettings');
    }

    /**
     * @return services\TypeAssociations
     */
    public function getTypeAssociations()
    {
        return $this->get('typeAssociations');
    }

    /**
     * @return services\UserCategories
     */
    public function getUserCategories()
    {
        return $this->get('userCategories');
    }

    /**
     * @return services\UserCategoryAssociations
     */
    public function getUserCategoryAssociations()
    {
        return $this->get('userCategoryAssociations');
    }

    /**
     * @return services\Users
     */
    public function getUsers()
    {
        return $this->get('users');
    }

    /**
     * @return services\UserAssociations
     */
    public function getUserAssociations()
    {
        return $this->get('userAssociations');
    }

    /*******************************************
     * LOGGING
     *******************************************/

    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function trace($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_TRACE, self::normalizeCategory($category));
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function error($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, self::normalizeCategory($category));
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function warning($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_WARNING, self::normalizeCategory($category));
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function info($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, self::normalizeCategory($category));
    }

    /**
     * @param string|null $category
     * @return string
     */
    private static function normalizeCategory(string $category = null)
    {
        $normalizedCategory = 'Organizations';

        if ($category === null) {
            return $normalizedCategory;
        }

        return $normalizedCategory . ': ' . $category;
    }
}
