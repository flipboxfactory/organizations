<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\traits;

use craft\elements\User as UserElement;
use flipbox\ember\helpers\ModelHelper;

/**
 * @property int|null $userId
 * @property UserElement|null $user
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationRules
{
    /**
     * @return array
     */
    protected function organizationRules(): array
    {
        return [
            [
                [
                    'organizationId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'organizationId',
                    'organization'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}