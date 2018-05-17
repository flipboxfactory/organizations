<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\traits\IdAttribute;
use flipbox\ember\records\traits\UserAttribute;
use flipbox\organizations\db\UserAssociationQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use yii\db\ActiveQueryInterface;
use flipbox\ember\records\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $organizationId
 * @property int $organizationOrder The order which an organization lists its users
 * @property int $userOrder The order which a user lists its organizations
 * @property Organization $organization
 * @property UserType[] $types
 */
class UserAssociation extends ActiveRecord implements SortableAssociationInterface
{
    use UserAttribute,
        IdAttribute,
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
    public static function find()
    {
        return Craft::createObject(UserAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function associate(bool $autoReorder = true): bool
    {
        return OrganizationPlugin::getInstance()->getUserOrganizationAssociations()->associate(
            $this,
            $autoReorder
        );
    }

    /**
     * @inheritdoc
     */
    public function dissociate(bool $autoReorder = true): bool
    {
        return OrganizationPlugin::getInstance()->getUserOrganizationAssociations()->dissociate(
            $this,
            $autoReorder
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->idRules(),
            $this->userRules(),
            $this->organizationRules(),
            $this->auditRules(),
            [
                [
                    [
                        static::SOURCE_ATTRIBUTE,
                        static::TARGET_ATTRIBUTE,
                    ],
                    'required'
                ],
                [
                    [
                        'userOrder',
                        'organizationOrder'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        static::SOURCE_ATTRIBUTE,
                        static::TARGET_ATTRIBUTE,
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
     * @return ActiveQueryInterface
     */
    public function getTypes(): ActiveQueryInterface
    {
        // Todo - order this by the sortOrder
        return $this->hasMany(UserType::class, ['id' => 'typeId'])
            ->viaTable(
                UserTypeAssociation::tableName(),
                ['userId' => 'id']
            );
    }
}
