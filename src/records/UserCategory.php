<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\records;

use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\traits\HandleRules;
use flipbox\organization\db\UserCategoryQuery;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $name
 */
class UserCategory extends ActiveRecordWithId
{
    use HandleRules;

    /**
     * The table name
     */
    const TABLE_ALIAS = Organization::TABLE_ALIAS . '_user_categories';

    /**
     * @inheritdoc
     * @return UserCategoryQuery
     */
    public static function find()
    {
        return new UserCategoryQuery;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            [
                [
                    [
                        'name'
                    ],
                    'required'
                ],
                [
                    [
                        'name',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'handle'
                    ],
                    UniqueValidator::class
                ]
            ]
        );
    }
}
