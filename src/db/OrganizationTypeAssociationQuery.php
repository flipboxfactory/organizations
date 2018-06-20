<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\organizations\records\OrganizationTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationTypeAssociation one($db = null)
 * @method OrganizationTypeAssociation[] all($db = null)
 * @method OrganizationTypeAssociation[] getCachedResult($db = null)
 */
class OrganizationTypeAssociationQuery extends SortableAssociationQuery
{
    /**
     * @var int|int[]|false|null The source Id(s). Prefix Ids with "not " to exclude them.
     */
    public $typeId;

    /**
     * @var int|int[]|false|null The target Id(s). Prefix Ids with "not " to exclude them.
     */
    public $organizationId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->from === null) {
            $this->from([
                OrganizationTypeAssociation::tableName() . ' ' . OrganizationTypeAssociation::tableAlias()
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'typeId';
    }

    /**
     * @inheritdoc
     * return static
     */
    public function organization($value)
    {
        return $this->organizationId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function organizationId($value)
    {
        $this->organizationId = $value;
        return $this;
    }

    /**
     * @inheritdoc
     * return static
     */
    public function type($value)
    {
        return $this->typeId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function typeId($value)
    {
        $this->typeId = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function prepare($builder)
    {
        if ($this->typeId !== null) {
            $this->andWhere(Db::parseParam('typeId', $this->typeId));
        }

        if ($this->organizationId !== null) {
            $this->andWhere(Db::parseParam('organizationId', $this->organizationId));
        }

        return parent::prepare($builder);
    }
}
