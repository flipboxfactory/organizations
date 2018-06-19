<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\view;

use Craft;
use craft\elements\User as UserElement;
use craft\helpers\UrlHelper;
use craft\models\Site;
use flipbox\ember\helpers\SiteHelper;
use flipbox\organizations\actions\organizations\traits\Populate;
use flipbox\organizations\cp\controllers\traits\Sites;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\events\RegisterOrganizationActionsEvent;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\web\assets\organization\Organization as OrganizationAsset;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationsController extends AbstractController
{
    use Populate, Sites;

    /**
     * The template base path
     */
    const TEMPLATE_BASE = parent::TEMPLATE_BASE . '/organization';

    /**
     * @event RegisterOrganizationActionsEvent
     */
    const EVENT_REGISTER_ORGANIZATION_ACTIONS = 'registerOrganizationActions';

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = self::TEMPLATE_BASE . '/index';

    /**
     * The index view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_BASE . '/upsert';

    /**
     * @return \flipbox\organizations\services\Organizations
     */
    protected function elementService()
    {
        return $this->module->module->getOrganizations();
    }

    /**
     * @return Response
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['elementType'] = OrganizationElement::class;
        $variables['siteIds'] = $this->getSiteIds();

        return $this->renderTemplate(
            static::TEMPLATE_INDEX,
            $variables
        );
    }

    /**
     * @param null $identifier
     * @param OrganizationElement|null $organization
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionUpsert($identifier = null, OrganizationElement $organization = null)
    {
        // Site
        $site = $this->activeSiteFromRequest();

        // Organization
        if (null === $organization) {
            if (null === $identifier) {
                $organization = $this->elementService()->create();
            } else {
                $organization = $this->elementService()->get($identifier, $site->id);
            }
        }

        $type = $this->findActiveType($organization->getPrimaryType());
        if ($type !== null) {
            if (!$this->module->module->getSettings()->isSiteEnabled($site->id)) {
                throw new InvalidConfigException("Type is not enabled for site.");
            }
            $organization->setActiveType($type);
        }

        // Variables
        $variables = [];
        if (null === $organization->id) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $organization);
        }

        $variables['enabledSiteIds'] = $this->getEnabledSiteIds($organization);
        $variables['siteIds'] = $this->getSiteIds();
        $variables['showSites'] = $this->hasMultipleSites();
        $variables['siteUrl'] = $this->getSiteUrl($organization);

        $variables['fullPageForm'] = true;
        $variables['organization'] = $organization;
        $variables['actions'] = $this->getActions($organization);
        $variables['tabs'] = $this->getTabs($organization);

        // Allow switching between types
        Craft::$app->getView()->registerAssetBundle(OrganizationAsset::class);
        Craft::$app->getView()->registerJs('new Craft.OrganizationTypeSwitcher();');

        // The user select input criteria
        $variables['elementType'] = UserElement::class;
        $variables['usersInputJsClass'] = 'Craft.NestedElementIndexSelectInput';
        $variables['usersInputJs'] = $this->getUserInputJs($organization);
        $variables['usersIndexJsClass'] = 'Craft.OrganizationUserIndex';
        $variables['usersIndexJs'] = $this->getUserIndexJs($organization);

        return $this->renderTemplate(
            static::TEMPLATE_UPSERT,
            $variables
        );
    }

    /**
     * @param OrganizationType|null $default
     * @return OrganizationType|mixed
     * @throws \flipbox\ember\exceptions\NotFoundException
     */
    private function findActiveType(OrganizationType $default = null)
    {
        $type = Craft::$app->getRequest()->getParam('type');
        if (!empty($type)) {
            $type = OrganizationPlugin::getInstance()->getOrganizationTypes()->get($type);
        }

        if ($type instanceof OrganizationType) {
            return $type;
        }

        return $default;
    }

    /**
     * @param OrganizationElement $organization
     * @return array
     */
    private function getActions(OrganizationElement $organization): array
    {
        $event = new RegisterOrganizationActionsEvent([
            'organization' => $organization,
            'destructiveActions' => [],
            'miscActions' => [],
        ]);
        $this->trigger(self::EVENT_REGISTER_ORGANIZATION_ACTIONS, $event);

        return array_filter([
            $event->miscActions,
            $event->destructiveActions,
        ]);
    }

    /*******************************************
     * JS CONFIGS
     *******************************************/

    /**
     * @param OrganizationElement $element
     * @return array
     */
    private function getUserIndexJs(OrganizationElement $element): array
    {
        return [
            'source' => 'nested',
            'context' => 'index',
            'showStatusMenu' => true,
            'showSiteMenu' => true,
            'hideSidebar' => false,
            'toolbarFixed' => false,
            'storageKey' => 'nested.index.organization.users',
            'updateElementsAction' => 'organizations/cp/user-indexes/get-elements',
            'submitActionsAction' => 'organizations/cp/user-indexes/perform-action',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element->siteId),
                'organization' => $element->getId()
            ],
            'viewParams' => [
                'organization' => $element->getId()
            ],
            'viewSettings' => [
                'loadMoreAction' => 'organizations/cp/user-indexes/get-more-elements'
            ]
        ];
    }

    /**
     * @param OrganizationElement $element
     * @return array
     */
    private function getUserInputJs(OrganizationElement $element): array
    {
        return [
            'elementType' => UserElement::class,
            'sources' => '*',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element->siteId)
            ],
            'sourceElementId' => $element->getId() ?: null,
            'viewMode' => 'list',
            'limit' => null,
            'selectionLabel' => Craft::t('organizations', "Add a user"),
            'storageKey' => 'nested.index.input.organization.users',
            'elements' => OrganizationPlugin::getInstance()->getUsers()->getQuery([
                'organization' => $element->getId(),
                'status' => null
            ])->ids(),
            'addAction' => 'organizations/cp/users/associate',
            'selectTargetAttribute' => 'user',
            'selectParams' => [
                'organization' => $element->getId() ?: null
            ]
        ];
    }

    /*******************************************
     * RESOLVE TYPE
     *******************************************/

    /**
     * @return Site
     * @throws Exception
     */
    private function activeSiteFromRequest(): Site
    {
        $siteSettings = $this->module->module->getSettings()->getSiteSettings();

        if (true === $this->hasMultipleSites()) {
            $siteSetting = reset($siteSettings);
            return $siteSetting->getSite();
        }

        $site = $this->resolveSiteFromRequest();

        if (array_key_exists($site->id, $siteSettings)) {
            return $site;
        }

        throw new Exception("Site is not enabled");
    }



    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return OrganizationPlugin::getInstance()->getUniqueId();
    }

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/organizations';
    }

    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param OrganizationElement $organization
     */
    protected function updateVariables(array &$variables, OrganizationElement $organization)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Craft::t('organizations', 'Edit') . ' ' . $organization->title;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $organization->getId());
        $variables['crumbs'][] = [
            'label' => $organization->title,
            'url' => UrlHelper::url($variables['continueEditingUrl'])
        ];
    }


    /**
     * Set base variables used to generate template views
     *
     * @param array $variables
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);

        // Types
        $variables['types'] = OrganizationPlugin::getInstance()->getOrganizationTypes()->findAll();
        $variables['typeOptions'] = [];
        /** @var OrganizationType $type */
        foreach ($variables['types'] as $type) {
            $variables['typeOptions'][] = [
                'label' => Craft::t('site', $type->name),
                'value' => $type->id
            ];
        }
    }
}
