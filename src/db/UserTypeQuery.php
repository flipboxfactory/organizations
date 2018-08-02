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
use flipbox\ember\db\traits\UserAttribute;
use flipbox\organizations\records\UserAssociation as OrganizationUsersRecord;
use flipbox\organizations\records\UserType as UserTypeRecord;
use flipbox\organizations\records\UserTypeAssociation as UserTypeAssociationsRecord;
use yii\base\ArrayableTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserTypeRecord one($db = null)
 * @method UserTypeRecord[] all($db = null)
 * @method UserTypeRecord[] getCachedResult($db = null)
 */
class UserTypeQuery extends CacheableActiveQuery
{
    use traits\OrganizationAttribute,
        UserAttribute,
        ArrayableTrait,
        FixedOrderBy,
        AuditAttributes;

    /**
     * Constructor.
     * @param array $config configurations to be applied to the newly created query object
     */
    public function __construct($config = [])
    {
        parent::__construct(UserTypeRecord::class, $config);
    }

    /**
     * @inheritdoc
     */
    public $orderBy = ['name' => SORT_ASC];

    /**
     * @var int|int[]|null
     */
    public $id;

    /**
     * @var string|string[]|null
     */
    public $handle;

    /**
     * @var string|string[]|null
     */
    public $name;

    /**
     * Flag if the table is already joined (to prevent subsequent joins)
     *
     * @var bool
     */
    private $typeAssociationTableJoined = false;

    /**
     * Flag if the table is already joined (to prevent subsequent joins)
     *
     * @var bool
     */
    private $userAssociationTableJoined = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->select === null) {
            $this->select = [UserTypeRecord::tableAlias() . '.*'];
        }

        // Set table name
        if ($this->from === null) {
            $this->from([UserTypeRecord::tableName() . ' ' . UserTypeRecord::tableAlias()]);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        // Reset
        $this->typeAssociationTableJoined = false;
        $this->userAssociationTableJoined = false;

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
        $attributes = ['id', 'handle', 'name'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->$attribute)) {
                $this->andWhere(Db::parseParam(UserTypeRecord::tableAlias() . '.' . $attribute, $value));
            }
        }
    }

    /**
     * Prepares relation params
     */
    protected function prepareRelationsParams()
    {
        if (empty($this->user) && empty($this->organization)) {
            return;
        }

        $alias = $this->joinOrganizationUserAssociationTable();

        $this->applyUserParam($alias);
        $this->applyOrganizationParam($alias);
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


    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @return string
     */
    protected function joinUserTypeAssociationsTable(): string
    {
        $alias = UserTypeAssociationsRecord::tableAlias();

        if ($this->typeAssociationTableJoined === false) {
            $this->leftJoin(
                UserTypeAssociationsRecord::tableName() . ' ' . $alias,
                '[[' . UserTypeRecord::tableAlias() . '.id]]=[[' . $alias . '.typeId]]'
            );

            $this->typeAssociationTableJoined = true;
        }

        return $alias;
    }

    /**
     * @return string
     */
    protected function joinOrganizationUserAssociationTable(): string
    {
        $userAlias = OrganizationUsersRecord::tableAlias();

        if ($this->userAssociationTableJoined === false) {
            $typeAlias = $this->joinUserTypeAssociationsTable();
            $this->leftJoin(
                OrganizationUsersRecord::tableName() . ' ' . $userAlias,
                '[[' . $userAlias . '.id]] = [[' . $typeAlias . '.userId]]'
            );

            $this->userAssociationTableJoined = true;
        }

        return $userAlias;
    }


    /************************************************************
     * USER
     ************************************************************/

    /**
     * @param string $alias
     */
    protected function applyUserParam(string $alias)
    {
        if (empty($this->user)) {
            return;
        }

        $this->andWhere(
            Db::parseParam($alias . '.userId', $this->parseUserValue($this->user))
        );
    }


    /************************************************************
     * ORGANIZATION
     ************************************************************/

    /**
     * @param string $alias
     */
    protected function applyOrganizationParam(string $alias)
    {
        if (empty($this->organization)) {
            return;
        }

        $this->andWhere(
            Db::parseParam($alias . '.organizationId', $this->parseOrganizationValue($this->organization))
        );
    }
}
