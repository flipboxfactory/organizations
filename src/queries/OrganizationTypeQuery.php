<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\organizations\records\OrganizationType as TypeRecord;
use flipbox\organizations\records\OrganizationTypeAssociation as TypeAssociationRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @inheritdoc
 *
 * @method TypeRecord one($db = null)
 * @method TypeRecord[] all($db = null)
 * @method TypeRecord[] getCachedResult($db = null)
 */
class OrganizationTypeQuery extends CacheableActiveQuery
{
    use OrganizationTypeAttributeTrait,
        OrganizationAttributeTrait,
        AuditAttributesTrait;

    /**
     * @var int|null
     */
    public $sortOrder;

    /**
     * @param $sortOrder
     * @return static
     */
    public function sortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @param $sortOrder
     * @return static
     */
    public function setSortOrder($sortOrder)
    {
        return $this->sortOrder($sortOrder);
    }

    /**
     * @var int|int[]|null
     */
    public $id;

    /**
     * @var string|string[]|null
     */
    public $name;

    /**
     * @var string|string[]|null
     */
    public $handle;

    /**
     * @var int|int[]|null
     */
    public $fieldLayoutId;

    /**
     * Flag if the table is already joined (to prevent subsequent joins)
     *
     * @var bool
     */
    private $associationTableJoined = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->select === null) {
            $this->select = [TypeRecord::tableAlias() . '.*'];
        }

        // Set table name
        if ($this->from === null) {
            $this->from([TypeRecord::tableName() . ' ' . TypeRecord::tableAlias()]);
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
        $this->applyAuditAttributeConditions();
        $this->prepareRelationsParams();
        $this->prepareAttributes();

        return parent::prepare($builder);
    }

    /**
     * Prepares simple attributes
     */
    protected function prepareAttributes()
    {
        $attributes = ['id', 'handle', 'name', 'sortOrder'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->{$attribute})) {
                $this->andWhere(Db::parseParam(TypeRecord::tableAlias() . '.' . $attribute, $value));
            }
        }
    }

    /**
     * Prepares relation params
     * @throws QueryAbortedException
     */
    protected function prepareRelationsParams()
    {
        // Is the query already doomed?
        if ($this->organization !== null && empty($this->organization)) {
            throw new QueryAbortedException();
        }

        if (empty($this->organization)) {
            return;
        }

        $alias = $this->joinTypeAssociationTable();
        $this->andWhere(
            Db::parseParam($alias . '.organizationId', $this->parseOrganizationValue($this->organization))
        );
    }

    /*******************************************
     * JOINS
     *******************************************/

    /**
     * @return string
     */
    protected function joinTypeAssociationTable(): string
    {
        $alias = TypeAssociationRecord::tableAlias();

        if ($this->associationTableJoined === false) {
            $this->leftJoin(
                TypeAssociationRecord::tableName() . ' ' . $alias,
                '[[' . $alias . '.typeId]] = [[' . TypeRecord::tableAlias() . '.id]]'
            );

            $this->associationTableJoined = true;
        }

        return $alias;
    }
}
