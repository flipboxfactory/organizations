<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations\types\traits;

use Craft;
use flipbox\ember\exceptions\ModelNotFoundException;
use flipbox\organizations\cp\actions\general\traits\SiteSettingAttributes;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\OrganizationType;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Populate
{
    use SiteSettingAttributes;

    /**
     * @param BaseObject $object
     * @return bool
     * @throws ModelNotFoundException
     */
    protected function ensureType(BaseObject $object): bool
    {
        if (!$object instanceof OrganizationType) {
            throw new ModelNotFoundException(sprintf(
                "Organization Type must be an instance of '%s', '%s' given.",
                OrganizationType::class,
                get_class($object)
            ));
        }

        return true;
    }

    /**
     * These are the default body params that we're accepting.  You can lock down specific Client attributes this way.
     *
     * @return array
     */
    protected function validBodyParams(): array
    {
        return [
            'name',
            'handle'
        ];
    }

    /**
     * @param OrganizationType $model
     * @return OrganizationType
     */
    private function populateSiteLayout(OrganizationType $model): OrganizationType
    {
        $layoutOverride = (bool) Craft::$app->getRequest()->getBodyParam('fieldLayoutOverride');

        if ($layoutOverride) {
            $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        } else {
            $fieldLayout = Organizations::getInstance()->getSettings()->getFieldLayout();
        }

        $model->setFieldLayout($fieldLayout);

        return $model;
    }

    /**
     * @param OrganizationType $model
     * @return OrganizationType
     */
    private function populateSiteSettings(OrganizationType $model): OrganizationType
    {
        if (null !== ($sites = $this->sitesSettingsFromBody())) {
            $enabledSites = [];

            foreach ($sites as $siteId => $siteConfig) {
                if (!($siteConfig['enabled'] ?? false)) {
                    continue;
                }

                $siteConfig['siteId'] = $siteId;
                $enabledSites[$siteId] = $siteConfig;
            }

            $model->setSiteSettings($enabledSites);
        }
        return $model;
    }
}
