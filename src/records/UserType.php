<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\organizations\queries\UserTypeQuery;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $name
 */
class UserType extends ActiveRecordWithId
{
    use HandleRulesTrait;

    /**
     * The table name
     */
    const TABLE_ALIAS = Organization::TABLE_ALIAS . '_user_types';

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @inheritdoc
     * @return UserTypeQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(UserTypeQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    protected static function findByCondition($condition)
    {
        if (is_numeric($condition)) {
            $condition = ['id' => $condition];
        }

        if (is_string($condition)) {
            $condition = ['handle' => $condition];
        }

        return parent::findByCondition($condition);
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

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->getAttribute('name');
    }

    /*******************************************
     * PROJECT CONFIG
     *******************************************/

    /**
     * Return an array suitable for Craft's Project config
     */
    public function toProjectConfig(): array
    {
        return $this->toArray([
            'handle',
            'name'
        ]);
    }
}
