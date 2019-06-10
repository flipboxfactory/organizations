<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\OrganizationTypeAssociationQuery;
use yii\helpers\Json;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $typeId
 * @property int $organizationId
 * @property int $sortOrder
 * @property OrganizationType $type
 * @property Organization $organization
 */
class OrganizationTypeAssociation extends ActiveRecord
{
    use SortableTrait,
        OrganizationTypeAttributeTrait,
        OrganizationAttributeTrait;

    /**
     * The table name
     */
    const TABLE_ALIAS = OrganizationType::TABLE_ALIAS . '_associations';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['typeId', 'organizationId'];

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @inheritdoc
     * @return OrganizationTypeAssociationQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(OrganizationTypeAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->typeRules(),
            $this->organizationRules(),
            [
                [
                    [
                        'typeId',
                        'organizationId'
                    ],
                    'required'
                ],
                [
                    [
                        'typeId'
                    ],
                    'unique',
                    'targetAttribute' => [
                        'typeId',
                        'organizationId'
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
                'organizationId' => $this->organizationId
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
        try {
            $this->autoReOrder(
                'typeId',
                [
                    'organizationId' => $this->organizationId
                ]
            );
        } catch (\Exception $e) {
            Organizations::error(
                sprintf(
                    "Exception caught while trying to reorder '%s'. Exception: [%s].",
                    (string)get_class($this),
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );
        }

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
                'organizationId' => $this->organizationId
            ]
        );

        parent::afterDelete();
    }
}
