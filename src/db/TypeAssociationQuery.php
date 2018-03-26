<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\db;

use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\organization\records\TypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method TypeAssociation one($db = null)
 * @method TypeAssociation[] all($db = null)
 * @method TypeAssociation[] getCachedResult($db = null)
 */
class TypeAssociationQuery extends SortableAssociationQuery
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
                TypeAssociation::tableName() . ' ' . TypeAssociation::tableAlias()
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
     * Apply conditions
     */
    protected function applyConditions()
    {
        if ($this->typeId !== null) {
            $this->andWhere(Db::parseParam('typeId', $this->typeId));
        }

        if ($this->organizationId !== null) {
            $this->andWhere(Db::parseParam('organizationId', $this->organizationId));
        }
    }
}
