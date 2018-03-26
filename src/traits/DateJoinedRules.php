<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\traits;

use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\ember\helpers\ModelHelper;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait DateJoinedRules
{
    /**
     * @inheritdoc
     */
    public function dateJoinedRules()
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
