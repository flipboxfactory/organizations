<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organization\cp\actions\organization;

use Craft;
use craft\errors\ElementNotFoundException;
use flipbox\ember\actions\element\traits\Lookup;
use flipbox\ember\actions\traits\CheckAccess;
use flipbox\ember\actions\traits\Populate;
use flipbox\organization\actions\organizations\traits\Populate as PopulateOrganization;
use flipbox\organization\cp\controllers\traits\Sites;
use flipbox\organization\elements\Organization as OrganizationElement;
use flipbox\organization\Organization;
use yii\base\Action;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SwitchType extends Action
{
    use Sites,
        Populate,
        PopulateOrganization,
        Lookup,
        CheckAccess {
        populate as parentPopulate;
    }

    /**
     * @param null $organization
     * @return array|mixed
     */
    public function run($organization = null)
    {
        $organization = null !== $organization ? $this->find($organization) : $this->create();
        return $this->runInternal($organization);
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    protected function find($identifier)
    {
        $site = $this->resolveSiteFromRequest();
        return Organization::getInstance()->getOrganizations()->find(
            $identifier,
            $site ? $site->id : null
        );
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    protected function create()
    {
        $site = $this->resolveSiteFromRequest();
        return Organization::getInstance()->getOrganizations()->create([
            'siteId' => $site ? $site->id : null
        ]);
    }

    /**
     * @inheritdoc
     * @param OrganizationElement $element
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
        if (!$this->ensureOrganization($element)) {
            throw new ElementNotFoundException();
        }

        $view = Craft::$app->getView();

        return [
            'tabsHtml' => $view->renderTemplate(
                '_includes/tabs',
                [
                    'tabs' => $this->getTabs($element, false)
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
                    'showSites' => $this->hasMultipleSites()
                ]
            ),
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
        ];
    }

    /**
     * @inheritdoc
     * @param OrganizationElement $object
     * @return OrganizationElement
     */
    public function populate(BaseObject $object): BaseObject
    {
        if (true === $this->ensureOrganization($object)) {
            $this->parentPopulate($object);
            $this->populateFromRequest($object);
        }

        return $object;
    }
}
