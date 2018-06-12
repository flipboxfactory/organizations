<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\web\twig\variables;

use flipbox\organizations\models\Settings;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\services\Organizations;
use yii\di\ServiceLocator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Organization extends ServiceLocator
{
    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct(array_merge(
            $config,
            [
                'components' => [
                    'elements' => OrganizationPlugin::getInstance()->getOrganizations(),
                    'organizationTypes' => OrganizationPlugin::getInstance()->getOrganizationTypes(),
                    'users' => OrganizationPlugin::getInstance()->getUsers(),
                    'userTypes' => OrganizationPlugin::getInstance()->getUserTypes()
                ]
            ]
        ));
    }

    /**
     * @return Organizations
     *
     * @deprecated
     */
    public function getOrganizations(): Organizations
    {
        return OrganizationPlugin::getInstance()->getOrganizations();
    }

    /**
     * Plugins settings which are accessed via 'craft.organizations.settings'
     *
     * @return Settings
     */
    public function getSettings()
    {
        return OrganizationPlugin::getInstance()->getSettings();
    }
}
