<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\sortable\associations\records\SortableAssociation;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\helpers\ModelHelper;
use flipbox\organizations\db\UserTypeAssociationQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $organizationId
 * @property int $sortOrder
 * @property Organization $organization
 */
class UserTypeAssociation extends SortableAssociation
{
    /**
     * The table name
     */
    const TABLE_ALIAS = UserType::TABLE_ALIAS . '_associations';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'typeId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'userId';

    /**
     * @inheritdoc
     */
    protected function associationService(): SortableAssociations
    {
        return OrganizationPlugin::getInstance()->getUserTypeAssociations();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Craft::createObject(UserTypeAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'userId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
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
                        'userId',
                        'typeId'
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
