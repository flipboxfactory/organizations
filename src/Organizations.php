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
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementSourcesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout as FieldLayoutModel;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\ember\modules\LoggerTrait;
use flipbox\organizations\db\behaviors\OrganizationAttributesToUserQueryBehavior;
use flipbox\organizations\elements\behaviors\UserTypesBehavior;
use flipbox\organizations\elements\behaviors\UserOrganizationsBehavior;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\fields\Organization as OrganizationField;
use flipbox\organizations\fields\OrganizationType as OrganizationTypeField;
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
     * @inheritdoc
     */
    public function init()
    {
        // Services
        $this->setComponents([
            'element' => services\Element::class,
            'organizations' => services\Organizations::class,
            'organizationTypes' => services\OrganizationTypes::class,
            'organizationTypeSettings' => services\OrganizationTypeSettings::class,
            'organizationTypeAssociations' => services\OrganizationTypeAssociations::class,
            'organizationUserAssociations' => services\OrganizationUserAssociations::class,
            'records' => services\Records::class,
            'users' => services\Users::class,
            'userOrganizationAssociations' => services\UserOrganizationAssociations::class,
            'userTypes' => services\UserTypes::class,
            'userTypeAssociations' => services\UserTypeAssociations::class,
        ]);

        // Sub-Modules
        $this->setModules([
            'cp' => cp\Cp::class
        ]);

        parent::init();

        // Fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OrganizationField::class;
                $event->types[] = OrganizationTypeField::class;
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
            UserQuery::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $e) {
                $e->behaviors['organization'] = OrganizationAttributesToUserQueryBehavior::class;
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

        // User (attach behavior)
        Event::on(
            User::class,
            User::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $e) {
                $e->behaviors['organizations'] = UserOrganizationsBehavior::class;
                $e->behaviors['types'] = UserTypesBehavior::class;
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

        // User Type sources
        Event::on(
            User::class,
            User::EVENT_REGISTER_SOURCES,
            function (RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    $event->sources[] = [
                        'heading' => "Organization Groups"
                    ];

                    $types = static::getInstance()->getUserTypes()->findAll();
                    foreach ($types as $type) {
                        $event->sources[] = [
                            'key' => 'type:' . $type->id,
                            'label' => Craft::t('organizations', $type->name),
                            'criteria' => ['organization' => ['userType' => $type->id]],
                            'hasThumbs' => true
                        ];
                    }
                }
            }
        );
    }

    /**
     * @return string
     */
    protected static function getLogFileName(): string
    {
        return 'organizations';
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
     * @return services\OrganizationTypes
     */
    public function getOrganizationTypes()
    {
        return $this->get('organizationTypes');
    }

    /**
     * @return services\OrganizationTypeSettings
     */
    public function getOrganizationTypeSettings()
    {
        return $this->get('organizationTypeSettings');
    }

    /**
     * @return services\OrganizationTypeAssociations
     */
    public function getOrganizationTypeAssociations()
    {
        return $this->get('organizationTypeAssociations');
    }

    /**
     * @return services\OrganizationUserAssociations
     */
    public function getOrganizationUserAssociations()
    {
        return $this->get('organizationUserAssociations');
    }

    /**
     * @return services\Records
     */
    public function getRecords()
    {
        return $this->get('records');
    }

    /**
     * @return services\Users
     */
    public function getUsers()
    {
        return $this->get('users');
    }

    /**
     * @return services\UserOrganizationAssociations
     */
    public function getUserOrganizationAssociations()
    {
        return $this->get('userOrganizationAssociations');
    }

    /**
     * @return services\UserTypes
     */
    public function getUserTypes()
    {
        return $this->get('userTypes');
    }

    /**
     * @return services\UserTypeAssociations
     */
    public function getUserTypeAssociations()
    {
        return $this->get('userTypeAssociations');
    }
}
