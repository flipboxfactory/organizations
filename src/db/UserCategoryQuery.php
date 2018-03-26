<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\db;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\ember\db\CacheableActiveQuery;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\FixedOrderBy;
use flipbox\ember\db\traits\UserAttribute;
use flipbox\organization\records\UserAssociation as OrganizationUsersRecord;
use flipbox\organization\records\UserCategory as UserCategoryRecord;
use flipbox\organization\records\UserCategoryAssociation as UserCategoryAssociationsRecord;
use yii\base\ArrayableTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserCategoryRecord one($db = null)
 * @method UserCategoryRecord[] all($db = null)
 * @method UserCategoryRecord[] getCachedResult($db = null)
 */
class UserCategoryQuery extends CacheableActiveQuery
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
        parent::__construct(UserCategoryRecord::class, $config);
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
    private $categoryAssociationTableJoined = false;

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
            $this->select = [UserCategoryRecord::tableAlias() . '.*'];
        }

        // Set table name
        if ($this->from === null) {
            $this->from([UserCategoryRecord::tableName() . ' ' . UserCategoryRecord::tableAlias()]);
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
        $attributes = ['id', 'handle', 'name'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->$attribute)) {
                $this->andWhere(Db::parseParam(UserCategoryRecord::tableAlias() . '.' . $attribute, $value));
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
    protected function joinUserCategoryAssociationsTable(): string
    {
        $alias = UserCategoryAssociationsRecord::tableAlias();

        if ($this->categoryAssociationTableJoined === false) {
            $this->leftJoin(
                UserCategoryAssociationsRecord::tableName() . ' ' . $alias,
                '[[' . UserCategoryRecord::tableAlias() . '.id]]=[[' . $alias . '.categoryId]]'
            );

            $this->categoryAssociationTableJoined = true;
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
            $categoryAlias = $this->joinUserCategoryAssociationsTable();
            $this->leftJoin(
                OrganizationUsersRecord::tableName() . ' ' . $userAlias,
                '[[' . $userAlias . '.id]] = [[' . $categoryAlias . '.userId]]'
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
