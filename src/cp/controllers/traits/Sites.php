<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organization\cp\controllers\traits;

use Craft;
use craft\base\Field;
use craft\models\FieldLayoutTab;
use flipbox\organization\elements\Organization as OrganizationElement;
use flipbox\organization\models\SiteSettings;
use flipbox\organization\Organization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Sites
{
    /**
     * @param OrganizationElement $organization
     * @param bool $includeUsers
     * @return array
     */
    protected function getTabs(OrganizationElement $organization, bool $includeUsers = true): array
    {
        $tabs = [];

        $count = 1;
        foreach ($organization->getFieldLayout()->getTabs() as $tab) {
            $tabs[] = $this->getTab($organization, $tab, $count++);
        }

        if (null !== $organization->getId() &&
            true === $includeUsers
        ) {
            $tabs['users'] = [
                'label' => Craft::t('organizations', 'Users'),
                'url' => '#user-index'
            ];
        }

        return $tabs;
    }

    /**
     * @param OrganizationElement $organization
     * @param FieldLayoutTab $tab
     * @param int $count
     * @return array
     */
    protected function getTab(OrganizationElement $organization, FieldLayoutTab $tab, int $count): array
    {
        $hasErrors = false;
        if ($organization->hasErrors()) {
            foreach ($tab->getFields() as $field) {
                /** @var Field $field */
                $hasErrors = $organization->getErrors($field->handle) ? true : $hasErrors;
            }
        }

        return [
            'label' => $tab->name,
            'url' => '#tab' . $count,
            'class' => $hasErrors ? 'error' : null
        ];
    }

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
        foreach ($this->resolveSites() as $siteSettings) {
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
        if ($this->hasMultipleSites() === false) {
            return [];
        }

        return array_keys(
            Organization::getInstance()->getSettings()->getSiteSettings()
        );
    }

    /**
     * @param OrganizationElement $organization
     * @return string
     */
    protected function getSiteUrl(OrganizationElement $organization): string
    {
        return Organization::getInstance()->getUniqueId() . '/' . $organization->getId() ?: 'new';
    }

    /**
     * @return bool
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function hasMultipleSites(): bool
    {
        $sites = $this->resolveSites();
        return true === Craft::$app->getIsMultiSite() && count($sites) > 1;
    }

    /**
     * @return SiteSettings[]
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function resolveSites(): array
    {
        return Organization::getInstance()->getSettings()->getSiteSettings();
    }
}
