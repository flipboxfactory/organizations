<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\models\Site;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\records\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait PopulateOrganizationTrait
{

    /**
     * @return array
     */
    protected function validBodyParams(): array
    {
        return [];
    }

    /*******************************************
     * POPULATE (from Request)
     *******************************************/

    /**
     * @param OrganizationElement $organization
     * @throws \Exception
     */
    protected function populateFromRequest(OrganizationElement $organization)
    {
        $request = Craft::$app->getRequest();

        // Title
        $organization->title = (string)$request->getBodyParam(
            'title',
            $organization->title
        );

        // Slug
        $organization->slug = (string)$request->getBodyParam(
            'slug',
            $organization->slug
        );

        // Enabled
        $organization->enabled = (bool)$request->getBodyParam(
            'enabled',
            $organization->enabled
        );

        // Enabled for Site
        $organization->enabledForSite = (bool)$request->getBodyParam(
            'enabledForSite',
            $organization->enabledForSite
        );

        // Site
        $this->populateSiteFromRequest($organization);

        // Join date
        $this->populateDateFromRequest($organization, 'dateJoined');

        // Active type
        $type = Craft::$app->getRequest()->getParam('type');
        if (!empty($type)) {
            $organization->setActiveType(
                OrganizationType::getOne($type)
            );
        }

        // Set types
        $organization->setTypesFromRequest(
            (string)$request->getParam('typesLocation', 'types')
        );

        // Set users
        $organization->setUsersFromRequest(
            (string)$request->getParam('usersLocation', 'users')
        );

        // Set content
        $organization->setFieldValuesFromRequest(
            (string)$request->getParam('fieldsLocation', 'fields')
        );
    }

    /**
     * @param OrganizationElement $organization
     * @return OrganizationElement
     */
    protected function populateSiteFromRequest(OrganizationElement $organization)
    {
        if ($site = $this->resolveSiteFromRequest()) {
            $organization->siteId = $site->id;
        }
        return $organization;
    }

    /**
     * @param OrganizationElement $organization
     * @param string $dateProperty
     * @throws \Exception
     */
    private function populateDateFromRequest(OrganizationElement $organization, string $dateProperty)
    {
        $dateTime = DateTimeHelper::toDateTime(
            Craft::$app->getRequest()->getBodyParam($dateProperty, $organization->{$dateProperty})
        );
        $organization->{$dateProperty} = $dateTime === false ? null : $dateTime;
    }

    /*******************************************
     * RESOLVE SITE
     *******************************************/

    /**
     * @return Site|null
     */
    protected function resolveSiteFromRequest()
    {
        if (!$site = Craft::$app->getRequest()->getParam('site')) {
            $site = Craft::$app->getSites()->currentSite;
        }

        return SiteHelper::resolve($site);
    }
}
