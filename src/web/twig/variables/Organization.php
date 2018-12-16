<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\web\twig\variables;

use craft\elements\db\UserQuery;
use craft\elements\User;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\queries\OrganizationTypeQuery;
use flipbox\organizations\queries\UserTypeQuery;
use flipbox\organizations\models\Settings;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\UserType;
use yii\di\ServiceLocator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Organization extends ServiceLocator
{
    /**
     * @param array $config
     * @return UserQuery
     */
    public function getUsers(array $config = []): UserQuery
    {
        $query = User::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }

    /**
     * @param array $config
     * @return OrganizationQuery
     */
    public function getOrganizations(array $config = []): OrganizationQuery
    {
        $query = OrganizationElement::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }

    /**
     * @param array $config
     * @return UserTypeQuery
     */
    public function getUserTypes(array $config = []): UserTypeQuery
    {
        $query = UserType::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }

    /**
     * @param array $config
     * @return OrganizationTypeQuery
     */
    public function getOrganizationTypes(array $config = []): OrganizationTypeQuery
    {
        $query = OrganizationType::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
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
