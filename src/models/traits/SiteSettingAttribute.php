<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\models\traits;

use Craft;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\organization\models\SiteSettings;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait SiteSettingAttribute
{
    /**
     * @var SiteSettings[]
     */
    private $siteSettings = [];

    /*******************************************
     * GETTER / SETTERS
     *******************************************/

    /**
     * @return SiteSettings[]
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSiteSettings(): array
    {
        $settings = $this->siteSettings;

        if (empty($settings)) {
            $this->addSite(['site' => Craft::$app->getSites()->getPrimarySite()]);
        }

        return $this->siteSettings;
    }

    /**
     * @param array $sites
     * @return $this
     */
    public function setSiteSettings(array $sites = [])
    {
        $this->siteSettings = [];
        foreach ($sites as $site) {
            $this->addSite($site);
        }
        return $this;
    }

    /**
     * @param array|SiteSettings $site
     * @return $this
     */
    private function addSite($site)
    {
        $site = $this->resolveSiteSettings($site);
        $this->siteSettings[$site->getSiteId()] = $site;
        return $this;
    }


    /**
     * @param $site
     * @return SiteSettings
     */
    protected function resolveSiteSettings($site): SiteSettings
    {
        if ($site instanceof SiteSettings) {
            return $site;
        }

        try {
            $object = Craft::createObject($site);
        } catch (\Exception $e) {
            $object = new SiteSettings();
            ObjectHelper::populate(
                $object,
                $site
            );
        }

        /** @var SiteSettings $object */
        return $object;
    }
}
