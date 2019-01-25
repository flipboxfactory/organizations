<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\Query;
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
        OrganizationTypeAttributeTrait;

    /**
     * @var mixed When the resulting organizations must have joined.
     */
    public $dateJoined;

    /**
     * @inheritdoc
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
     * Prepares relation params
     */
    protected function prepareRelationsParams()
    {
        // Type
        $this->applyTypeParam();

        if (empty($this->user) && empty($this->userGroup) && empty($this->userType)) {
            return;
        }

        $alias = $this->joinOrganizationUserTable();

        $this->applyUserParam($alias);
        $this->applyUserGroupParam($alias);
        $this->applyUserTypeParam($alias);
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
     */
    protected function applyUserParam(string $alias)
    {
        if (empty($this->user)) {
            return;
        }

        $this->subQuery->andWhere(
            Db::parseParam($alias . '.userId', $this->parseUserValue($this->user))
        );
        $this->subQuery->distinct(true);
    }


    /************************************************************
     * USER GROUP
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     */
    protected function applyUserGroupParam(string $alias)
    {
        if (empty($this->userGroup)) {
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
     * USER COLLECTION
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     */
    protected function applyUserTypeParam(string $alias)
    {
        if (empty($this->userType)) {
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
     * TYPE
     ************************************************************/

    /**
     * @return void
     */
    protected function applyTypeParam()
    {
        if (empty($this->organizationType)) {
            return;
        }

        $alias = $this->joinOrganizationTypeTable();

        $this->subQuery
            ->distinct(true)
            ->andWhere(
            Db::parseParam($alias . '.typeId', $this->parseOrganizationTypeValue($this->organizationType))
        );
    }
}
