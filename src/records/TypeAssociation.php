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
use flipbox\organization\db\TypeAssociationQuery;
use flipbox\organization\Organization as OrganizationPlugin;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $typeId
 * @property int $organizationId
 * @property int $sortOrder
 * @property Type $type
 * @property Organization $organization
 */
class TypeAssociation extends SortableAssociation
{
    use traits\TypeAttribute,
        traits\OrganizationAttribute;

    /**
     * The table name
     */
    const TABLE_ALIAS = Type::TABLE_ALIAS . '_associations';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'typeId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'organizationId';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['typeId', 'organizationId'];

    /**
     * @inheritdoc
     */
    protected function associationService(): SortableAssociations
    {
        return OrganizationPlugin::getInstance()->getTypeAssociations();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Craft::createObject(TypeAssociationQuery::class, [get_called_class()]);
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
}
