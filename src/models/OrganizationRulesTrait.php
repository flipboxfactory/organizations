<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\models;

use craft\elements\User as UserElement;
use yii\base\Model;

/**
 * @property int|null $userId
 * @property UserElement|null $user
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationRulesTrait
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
                    Model::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
