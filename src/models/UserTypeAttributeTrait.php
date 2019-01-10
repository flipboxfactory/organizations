<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use Craft;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserTypeAttributeTrait
{
    use UserTypeRulesTrait,
        \flipbox\organizations\objects\UserTypeAttributeTrait;


    /**
     * @return array
     */
    protected function typeAttributes(): array
    {
        return [
            'typeId'
        ];
    }

    /**
     * @return array
     */
    protected function typeAttributeLabels(): array
    {
        return [
            'typeId' => Craft::t('organizations', 'User Type Id')
        ];
    }
}
