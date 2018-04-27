<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\traits;

use flipbox\ember\helpers\ModelHelper;
use flipbox\organizations\records\OrganizationType as TypeModel;

/**
 * @property int|null $typeId
 * @property TypeModel|null $type
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TypeRules
{
    /**
     * @return array
     */
    protected function typeRules(): array
    {
        return [
            [
                [
                    'typeId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'typeId',
                    'type'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
