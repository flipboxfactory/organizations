<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\elements\db\UserQuery;
use craft\helpers\Db;
use flipbox\organizations\records\UserAssociation as OrganizationUsersRecord;
use flipbox\organizations\records\UserTypeAssociation as UserCollectionUsersRecord;
use yii\base\BaseObject;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserQueryParamHandler extends BaseObject
{
    use OrganizationAttributeTrait,
        OrganizationTypeAttributeTrait,
        UserTypeAttributeTrait {
        setUserType as parentSetUserType;
    }

    /**
     * @var OrganizationAttributesToUserQueryBehavior
     */
    private $owner;

    /**
     * @var string|string[]|null
     */
    public $userState;

    /**
     * @param string|string[]|null $value
     * @return UserQuery
     */
    public function setUserState($value): UserQuery
    {
        $this->userState = $value;
        return $this->owner->owner;
    }

    /**
     * @param string|string[]|null $value
     * @return UserQuery
     */
    public function userState($value): UserQuery
    {
        return $this->setUserState($value);
    }

    /**
     * @inheritdoc
     * @param OrganizationAttributesToUserQueryBehavior $owner
     */
    public function __construct(OrganizationAttributesToUserQueryBehavior $owner, array $config = [])
    {
        $this->owner = $owner;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function setUserType($value): UserQuery
    {
        $this->parentSetUserType($value);
        return $this->owner->owner;
    }

    /**
     * @param UserQuery $query
     */
    public function applyParams(UserQuery $query)
    {
        if ($query->subQuery === null ||
            (
                $this->organization === null &&
                $this->organizationType === null &&
                $this->userType === null &&
                $this->userState === null
            )
        ) {
            return;
        }

        $this->joinOrganizationUserTable($query);

        $this->applyOrganizationParam(
            $query,
            $this->organization
        );

        $this->applyUserTypeParam(
            $query,
            $this->userType
        );

        $this->applyUserStateParam(
            $query,
            $this->userState
        );
    }

    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @inheritdoc
     */
    protected function joinOrganizationUserTable(UserQuery $query)
    {
        $alias = OrganizationUsersRecord::tableAlias();

        $query->subQuery->leftJoin(
            OrganizationUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.userId]] = [[elements.id]]'
        );

        // Check if we're ordering by one of the association table's order columns
        if (is_array($query->orderBy)) {
            $columns = ['userOrder' => 'userOrder', 'organizationOrder' => 'organizationOrder'];
            $matches = array_intersect_key($columns, $query->orderBy);

            foreach ($matches as $param => $select) {
                $query->subQuery->addSelect([$alias . '.' . $select]);
            }
        }

        return $alias;
    }

    /**
     * @inheritdoc
     */
    protected function joinOrganizationUserTypeTable(Query $query)
    {
        $alias = UserCollectionUsersRecord::tableAlias();
        $query->leftJoin(
            UserCollectionUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.userId]] = [[' . OrganizationUsersRecord::tableAlias() . '.id]]'
        );
    }


    /************************************************************
     * ORGANIZATION
     ************************************************************/

    /**
     * @param UserQuery $query
     * @param $organization
     */
    protected function applyOrganizationParam(UserQuery $query, $organization)
    {
        if (empty($organization)) {
            return;
        }

        $query->subQuery->andWhere(
            Db::parseParam(
                OrganizationUsersRecord::tableAlias() . '.organizationId',
                $this->parseOrganizationValue($organization)
            )
        );
    }


    /************************************************************
     * USER TYPE
     ************************************************************/

    /**
     * @param UserQuery $query
     * @param $type
     */
    protected function applyUserTypeParam(UserQuery $query, $type)
    {
        if (empty($type)) {
            return;
        }

        $this->joinOrganizationUserTypeTable($query->subQuery);
        $query->subQuery
            ->distinct(true)
            ->andWhere(
                Db::parseParam(
                    UserCollectionUsersRecord::tableAlias() . '.typeId',
                    $this->parseUserTypeValue($type)
                )
            );
    }


    /************************************************************
     * USER STATE
     ************************************************************/

    /**
     * @param UserQuery $query
     * @param $state
     */
    protected function applyUserStateParam(UserQuery $query, $state)
    {
        if (empty($state)) {
            return;
        }

        $query->subQuery->andWhere(
            Db::parseParam(
                OrganizationUsersRecord::tableAlias() . '.state',
                $state
            )
        );
    }
}
