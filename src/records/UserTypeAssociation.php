<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\organizations\queries\UserTypeAssociationQuery;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $userId
 * @property int $typeId
 * @property int $sortOrder
 */
class UserTypeAssociation extends ActiveRecord
{
    use UserTypeAttributeTrait,
        SortableTrait;

    /**
     * The table name
     */
    const TABLE_ALIAS = UserType::TABLE_ALIAS . '_associations';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['typeId'];

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @inheritdoc
     * @return UserTypeAssociationQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(UserTypeAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->typeRules(),
            [
                [
                    [
                        'userId'
                    ],
                    'unique',
                    'targetAttribute' => [
                        'userId',
                        'typeId'
                    ]
                ],
                [
                    [
                        'userId'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->ensureSortOrder(
            [
                'userId' => $this->userId
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->autoReOrder(
            'typeId',
            [
                'userId' => $this->userId
            ]
        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->sequentialOrder(
            'typeId',
            [
                'userId' => $this->userId
            ]
        );

        parent::afterDelete();
    }

    /**
     * Returns the user association.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getUserAssociations(): ActiveQueryInterface
    {
        return $this->hasOne(UserAssociation::class, ['id' => 'userId']);
    }

    /**
     * Returns the type association.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getType(): ActiveQueryInterface
    {
        return $this->hasOne(UserType::class, ['id' => 'typeId']);
    }
}
