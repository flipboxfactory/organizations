<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\types\traits;

use Craft;
use flipbox\ember\exceptions\ModelNotFoundException;
use flipbox\organizations\cp\actions\general\traits\SiteSettingAttributes;
use flipbox\organizations\records\Type;
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
        if (!$object instanceof Type) {
            throw new ModelNotFoundException(sprintf(
                "Organization Type must be an instance of '%s', '%s' given.",
                Type::class,
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
     * @param Type $model
     * @return Type
     */
    private function populateSiteLayout(Type $model): Type
    {
        if ($fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost()) {
            $model->setFieldLayout($fieldLayout);
        }

        return $model;
    }

    /**
     * @param Type $model
     * @return Type
     */
    private function populateSiteSettings(Type $model): Type
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
