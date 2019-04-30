<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\cp\actions\organization;

use Craft;
use flipbox\craft\ember\actions\CheckAccessTrait;
use flipbox\craft\ember\actions\LookupTrait;
use flipbox\craft\ember\actions\PopulateTrait;
use flipbox\organizations\actions\organizations\PopulateOrganizationTrait;
use flipbox\organizations\cp\controllers\OrganizationTabsTrait;
use flipbox\organizations\cp\controllers\OrganizationSitesTrait;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\elements\Organization as OrganizationElement;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SwitchType extends Action
{
    use OrganizationSitesTrait,
        OrganizationTabsTrait,
        PopulateOrganizationTrait,
        PopulateTrait,
        LookupTrait,
        CheckAccessTrait {
        populate as parentPopulate;
    }

    /**
     * @param null $organization
     * @return array|mixed
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function run($organization = null)
    {
        $organization = (null !== $organization) ? $this->find($organization) : $this->create();
        return $this->runInternal($organization);
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    protected function find($identifier)
    {
        $site = $this->resolveSiteFromRequest();
        return Organization::findOne([
            is_numeric($identifier) ? 'id' : 'slug' => $identifier,
            $site ? $site->id : null
        ]);
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    protected function create()
    {
        $site = $this->resolveSiteFromRequest();
        return new Organization([
            'siteId' => $site ? $site->id : null
        ]);
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(OrganizationElement $element)
    {
        // Check access
        if (($access = $this->checkAccess($element)) !== true) {
            return $access;
        }

        return $this->data(
            $this->populate($element)
        );
    }

    /**
     * @param OrganizationElement $element
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    protected function data(OrganizationElement $element): array
    {
        $view = Craft::$app->getView();

        return [
            'tabsHtml' => $view->renderTemplate(
                '_includes/tabs',
                [
                    'tabs' => $this->getTabs($element, true)
                ]
            ),
            'fieldsHtml' => $view->renderTemplate(
                'organizations/_cp/organization/__fields',
                [
                    'element' => $element,
                    'fieldLayout' => $element->getFieldLayout()
                ]
            ),
            'sitesHtml' => $view->renderTemplate(
                'organizations/_cp/organization/__sites',
                [
                    'element' => $element,
                    'enabledSiteIds' => $this->getEnabledSiteIds($element),
                    'siteIds' => $this->getSiteIds(),
                    'url' => $this->getSiteUrl($element)
                ]
            ),
            'sidebarHtml' => $view->renderTemplate(
                'organizations/_cp/organization/__enabled',
                [
                    'element' => $element,
                    'showSites' => $this->showSites()
                ]
            ),
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
        ];
    }

    /**
     * @inheritdoc
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     */
    public function populate(OrganizationElement $element): OrganizationElement
    {
        $this->parentPopulate($element);
        $this->populateFromRequest($element);

        return $element;
    }
}
