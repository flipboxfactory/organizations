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
use flipbox\ember\records\traits\UserAttribute;
use flipbox\organization\db\UserAssociationQuery;
use flipbox\organization\Organizations as OrganizationPlugin;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $organizationId
 * @property int $sortOrder
 * @property Organization $organization
 * @property UserCategory[] $categories
 */
class UserAssociation extends SortableAssociation
{
    use UserAttribute,
        traits\OrganizationAttribute;

    /**
     * The table name
     */
    const TABLE_ALIAS = Organization::TABLE_ALIAS . '_user_associations';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'userId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'organizationId';

    /**
     * @inheritdoc
     */
    protected function associationService(): SortableAssociations
    {
        return OrganizationPlugin::getInstance()->getUserAssociations();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Craft::createObject(UserAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            $this->userRules(),
            $this->organizationRules(),
            [
                [
                    [
                        'userId'
                    ],
                    'unique',
                    'targetAttribute' => [
                        'userId',
                        'organizationId'
                    ]
                ]
            ]
        );
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getCategories(): ActiveQueryInterface
    {
        // Todo - order this by the sortOrder
        return $this->hasMany(UserCategory::class, ['id' => 'categoryId'])
            ->viaTable(
                UserCategoryAssociation::tableName(),
                ['userId' => 'id']
            );
    }
}
