<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\records;

use Craft;
use flipbox\craft\sortable\associations\records\SortableAssociation;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\helpers\ModelHelper;
use flipbox\organization\db\UserCategoryAssociationQuery;
use flipbox\organization\Organizations as OrganizationPlugin;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $organizationId
 * @property int $sortOrder
 * @property Organization $organization
 */
class UserCategoryAssociation extends SortableAssociation
{
    /**
     * The table name
     */
    const TABLE_ALIAS = UserCategory::TABLE_ALIAS . '_associations';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'categoryId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'userId';

    /**
     * @inheritdoc
     */
    protected function associationService(): SortableAssociations
    {
        return OrganizationPlugin::getInstance()->getUserCategoryAssociations();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Craft::createObject(UserCategoryAssociationQuery::class, [get_called_class()]);
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
                        'categoryId'
                    ]
                ],
                [
                    [
                        'userId',
                        'categoryId'
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
     * Returns the category association.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getCategory(): ActiveQueryInterface
    {
        return $this->hasOne(UserCategory::class, ['id' => 'categoryId']);
    }
}
