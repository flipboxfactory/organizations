<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\ember\db\CacheableActiveQuery;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\FixedOrderBy;
use flipbox\organizations\records\Type;
use flipbox\organizations\records\Type as TypeRecord;
use flipbox\organizations\records\TypeAssociation as TypeAssociationRecord;
use yii\base\ArrayableTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method TypeRecord one($db = null)
 * @method TypeRecord[] all($db = null)
 * @method TypeRecord[] getCachedResult($db = null)
 */
class TypeQuery extends CacheableActiveQuery
{
    use traits\TypeAttribute,
        traits\OrganizationAttribute,
        ArrayableTrait,
        FixedOrderBy,
        AuditAttributes;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct(Type::class, $config);
    }

    /**
     * @inheritdoc
     */
    public $orderBy = ['dateCreated' => SORT_ASC];

    /**
     * @var int|int[]|null
     */
    public $id;

    /**
     * @var int|int[]|null
     */
    public $sortOrder;

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
        parent::init();

        if ($this->select === null) {
            $this->select = [TypeRecord::tableAlias() . '.*'];
        }

        // Set table name
        if ($this->from === null) {
            $this->from([TypeRecord::tableName() . ' ' . TypeRecord::tableAlias()]);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        $this->applyAuditAttributeConditions();
        $this->applyOrderByParams($builder->db);
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
            if (null !== ($value = $this->$attribute)) {
                $this->andWhere(Db::parseParam(TypeRecord::tableAlias() . '.' . $attribute, $value));
            }
        }
    }

    /**
     * Prepares relation params
     */
    protected function prepareRelationsParams()
    {
        if (empty($this->organization)) {
            return;
        }

        $this->applyOrganizationParam($this, $this->organization);
    }

    /*******************************************
     * FIXED ORDER
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'id';
    }

    /*******************************************
     * JOINS
     *******************************************/

    /**
     * @param TypeQuery $query
     * @return string
     */
    protected function joinTypeAssociationTable(TypeQuery $query): string
    {
        $alias = TypeAssociationRecord::tableAlias();

        if ($this->associationTableJoined === false) {
            $query->leftJoin(
                TypeAssociationRecord::tableName() . ' ' . $alias,
                '[[' . $alias . '.typeId]] = [[' . TypeRecord::tableAlias() . '.id]]'
            );

            $this->associationTableJoined = true;
        }

        return $alias;
    }

    /*******************************************
     * PARAMS
     *******************************************/

    /**
     * @param TypeQuery $query
     * @param $organization
     */
    protected function applyOrganizationParam(TypeQuery $query, $organization)
    {
        if (empty($organization)) {
            return;
        }

        $alias = $this->joinTypeAssociationTable($query);
        $query->andWhere(
            Db::parseParam($alias . '.organizationId', $this->parseOrganizationValue($organization))
        );
    }
}
