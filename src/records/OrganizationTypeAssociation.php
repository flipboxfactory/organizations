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
use flipbox\organizations\db\OrganizationTypeAssociationQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;

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
class OrganizationTypeAssociation extends SortableAssociation
{
    use traits\OrganizationTypeAttribute,
        traits\OrganizationAttribute;

    /**
     * The table name
     */
    const TABLE_ALIAS = OrganizationType::TABLE_ALIAS . '_associations';

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
        return OrganizationPlugin::getInstance()->getOrganizationTypeAssociations();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
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
