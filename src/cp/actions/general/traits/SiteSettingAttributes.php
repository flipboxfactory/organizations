<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\actions\general\traits;

use Craft;
use craft\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait SiteSettingAttributes
{
    /**
     * Normalize site values from Body
     *
     * @return array|null
     */
    protected function sitesSettingsFromBody()
    {
        if (null === ($rawSites = Craft::$app->getRequest()->getBodyParam('siteSettings'))) {
            return null;
        }

        if (!is_array($rawSites)) {
            $rawSites = [$rawSites];
        }

        $sitesArray = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $sitesArray[$site->id] = $this->normalizeSiteConfig(
                ArrayHelper::getValue(
                    $rawSites,
                    $site->handle,
                    []
                )
            );
        }

        return $sitesArray ?? null;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function normalizeSiteConfig($config = []): array
    {
        return [
            'enabled' => !empty($config['enabled'] ?? null),
            'hasUrls' => !empty($config['uriFormat'] ?? null),
            'uriFormat' => $config['uriFormat'] ?? null,
            'template' => $config['template'] ?? null
        ];
    }
}
