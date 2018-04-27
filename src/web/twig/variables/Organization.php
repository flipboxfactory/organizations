<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\web\twig\variables;

use flipbox\organizations\models\Settings;
use flipbox\organizations\Organizations as OrganizationPlugin;
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
                    'organizations' => OrganizationPlugin::getInstance()->getOrganizations(),
                    'types' => OrganizationPlugin::getInstance()->getTypes(),
                    'users' => OrganizationPlugin::getInstance()->getUsers(),
                    'userTypes' => OrganizationPlugin::getInstance()->getUserTypes()
                ]
            ]
        ));
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
