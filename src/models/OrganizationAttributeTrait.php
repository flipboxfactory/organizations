<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\models;

use flipbox\organizations\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait OrganizationAttributeTrait
{
    use OrganizationRulesTrait, \flipbox\organizations\objects\OrganizationAttributeTrait;

    /**
     * @return array
     */
    protected function organizationAttributes(): array
    {
        return [
            'organizationId'
        ];
    }

    /**
     * @return array
     */
    protected function organizationAttributeLabels(): array
    {
        return [
            'organizationId' => Organizations::t('Organization Id')
        ];
    }
}
