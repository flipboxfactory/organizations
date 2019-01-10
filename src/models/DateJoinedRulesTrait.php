<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\craft\ember\helpers\ModelHelper;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait DateJoinedRulesTrait
{
    /**
     * @inheritdoc
     */
    protected function dateJoinedRules()
    {
        return [
            [
                [
                    'dateJoined'
                ],
                DateTimeValidator::class
            ],
            [
                [
                    'dateJoined'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
