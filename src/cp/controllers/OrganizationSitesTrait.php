<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\cp\controllers;

use Craft;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationSitesTrait
{
    /**
     * @param OrganizationElement $organization
     * @return int[]
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function getEnabledSiteIds(OrganizationElement $organization): array
    {
        if ($organization->id !== null) {
            return Craft::$app->getElements()->getEnabledSiteIdsForElement($organization->id);
        }

        $enabledSiteIds = [];
        foreach (Organizations::getInstance()->getSettings()->getSiteSettings() as $siteSettings) {
            if ($siteSettings->enabledByDefault) {
                $enabledSiteIds[] = $siteSettings->getSiteId();
            }
        }

        return $enabledSiteIds;
    }

    /**
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function getSiteIds(): array
    {
        if (Craft::$app->getIsMultiSite() === false) {
            return [];
        }

        $sites = Organizations::getInstance()->getSettings()->getSiteSettings();

        if (count($sites) <= 1) {
            return [];
        }

        return array_keys(
            Organizations::getInstance()->getSettings()->getSiteSettings()
        );
    }

    /**
     * @param OrganizationElement $organization
     * @return string
     */
    protected function getSiteUrl(OrganizationElement $organization): string
    {
        return Organizations::getInstance()->getUniqueId() . '/' . ($organization->getId() ?: 'new');
    }

    /**
     * @return bool
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function showSites(): bool
    {
        if (Craft::$app->getIsMultiSite() === false) {
            return false;
        }

        $sites = Organizations::getInstance()->getSettings()->getSiteSettings();

        if (count($sites) <= 1) {
            return false;
        }

        return true;
    }
}
