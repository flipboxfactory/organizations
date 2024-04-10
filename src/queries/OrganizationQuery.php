<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use craft\records\UserGroup_User as UserGroupUsersRecord;
use flipbox\craft\ember\queries\UserAttributeTrait;
use flipbox\craft\ember\queries\UserGroupAttributeTrait;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\records\Organization as OrganizationRecord;
use flipbox\organizations\records\OrganizationTypeAssociation as TypeAssociationRecord;
use flipbox\organizations\records\UserAssociation as OrganizationUsersRecord;
use flipbox\organizations\records\UserTypeAssociation as UserTypeAssociationRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Query $query
 * @property Query $subQuery
 *
 * @method OrganizationElement one($db = null)
 * @method OrganizationElement[] all($db = null)
 * @method OrganizationElement[] getCachedResult()
 */
class OrganizationQuery extends ElementQuery
{
    use UserAttributeTrait,
        UserGroupAttributeTrait,
        UserTypeAttributeTrait,
        UserStateAttributeTrait,
        OrganizationTypeAttributeTrait;

    /**
     * @var mixed When the resulting organizations must have joined.
     */
    public $dateJoined;

    /**
     * @inheritdoc
     */
    protected $defaultOrderBy = ['dateJoined' => SORT_DESC];

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    protected function beforePrepare(): bool
    {
        if (false === ($result = parent::beforePrepare())) {
            return false;
        }

        $alias = OrganizationRecord::tableAlias();
        $this->joinElementTable($alias);

        $this->query->select([
            $alias . '.dateJoined'
        ]);

        $this->prepareRelationsParams();
        $this->prepareAttributes($alias);

        return true;
    }

    /**
     * Prepares simple attributes
     *
     * @var string $alias
     */
    protected function prepareAttributes(string $alias)
    {
        if ($this->dateJoined) {
            $this->subQuery->andWhere(Db::parseDateParam($alias . '.dateJoined', $this->dateJoined));
        }
    }

    /**
     * @throws QueryAbortedException
     */
    protected function prepareRelationsParams()
    {
        // Type
        $this->applyTypeParam();

        if (is_null($this->user) && is_null($this->userGroup) && is_null($this->userType)) {
            return;
        }

        $alias = $this->joinOrganizationUserTable();

        $this->applyUserParam($alias);
        $this->applyUserGroupParam($alias);
        $this->applyUserTypeParam($alias);
        $this->applyUserStateParam($alias);
    }


    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @return string
     */
    protected function joinOrganizationUserTable(): string
    {
        $alias = OrganizationUsersRecord::tableAlias();

        $this->subQuery->innerJoin(
            OrganizationUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.organizationId]] = [[elements.id]]'
        );

        // Check if we're ordering by one of the association tables order columns
        if (is_array($this->orderBy)) {
            $columns = ['userOrder' => 'userOrder', 'organizationOrder' => 'organizationOrder'];
            $matches = array_intersect_key($columns, $this->orderBy);

            foreach ($matches as $param => $select) {
                $this->subQuery->addSelect([$alias . '.' . $select]);
            }
        }

        return $alias;
    }

    /**
     * @return string
     */
    protected function joinOrganizationTypeTable(): string
    {
        $alias = TypeAssociationRecord::tableAlias();

        $this->subQuery->leftJoin(
            TypeAssociationRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.organizationId]] = [[elements.id]]'
        );

        return $alias;
    }


    /************************************************************
     * USER
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserParam(string $alias)
    {
        // Is the query already doomed?
        if ($this->user !== null && empty($this->user)) {
            throw new QueryAbortedException();
        }

        if (is_null($this->user)) {
            return;
        }

        $this->subQuery->andWhere(
            Db::parseParam($alias . '.userId', $this->parseUserValue($this->user))
        );
    }


    /************************************************************
     * USER GROUP
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserGroupParam(string $alias)
    {
        // Is the query already doomed?
        if ($this->userGroup !== null && empty($this->userGroup)) {
            throw new QueryAbortedException();
        }

        if (is_null($this->userGroup)) {
            return;
        }

        $groupAlias = 'ug_user';

        $this->subQuery->innerJoin(
            UserGroupUsersRecord::tableName() . ' ' . $groupAlias,
            '[[' . $groupAlias . '.userId]] = [[' . $alias . '.userId]]'
        );

        $this->subQuery->andWhere(
            Db::parseParam($groupAlias . '.groupId', $this->parseUserGroupValue($this->userGroup))
        );
    }


    /************************************************************
     * USER TYPE
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserTypeParam(string $alias)
    {
        // Is the query already doomed?
        if ($this->userType !== null && empty($this->userType)) {
            throw new QueryAbortedException();
        }

        if (is_null($this->userType)) {
            return;
        }

        $typeAlias = 'ut_user';

        $this->subQuery->innerJoin(
            UserTypeAssociationRecord::tableName() . ' ' . $typeAlias,
            '[[' . $typeAlias . '.userId]] = [[' . $alias . '.userId]]'
        );

        $this->subQuery->andWhere(
            Db::parseParam($typeAlias . '.typeId', $this->parseUserTypeValue($this->userType))
        );
    }


    /************************************************************
     * USER STATE
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserStateParam(string $alias)
    {
        // Is the query already doomed?
        if ($this->userState !== null && empty($this->userState)) {
            throw new QueryAbortedException();
        }

        if (is_null($this->userState)) {
            return;
        }

        $this->subQuery->andWhere(
            Db::parseParam($alias . '.state', $this->userState)
        );
    }


    /************************************************************
     * TYPE
     ************************************************************/

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyTypeParam()
    {
        // Is the query already doomed?
        if ($this->organizationType !== null && empty($this->organizationType)) {
            throw new QueryAbortedException();
        }

        if (is_null($this->organizationType)) {
            return;
        }

        $alias = $this->joinOrganizationTypeTable();

        $this->subQuery
            ->andWhere(
                Db::parseParam($alias . '.typeId', $this->parseOrganizationTypeValue($this->organizationType))
            );
    }
}
