<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\db\objects;

use craft\elements\db\UserQuery;
use craft\helpers\Db;
use flipbox\organization\db\behaviors\OrganizationAttributesToUserQueryBehavior;
use flipbox\organization\db\traits\OrganizationAttribute;
use flipbox\organization\db\traits\TypeAttribute;
use flipbox\organization\db\traits\UserCategoryAttribute;
use flipbox\organization\records\UserAssociation as OrganizationUsersRecord;
use flipbox\organization\records\UserCategoryAssociation as UserCollectionUsersRecord;
use yii\base\BaseObject;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserQueryParamHandler extends BaseObject
{
    use OrganizationAttribute,
        TypeAttribute,
        UserCategoryAttribute {
        setType as parentSetType;
        setUserCategory as parentSetUserCategory;
    }

    /**
     * @var OrganizationAttributesToUserQueryBehavior
     */
    private $owner;

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
    public function setUserCategory($value): UserQuery
    {
        $this->parentSetUserCategory($value);
        return $this->owner->owner;
    }

    /**
     * @inheritdoc
     */
    public function setType($value): UserQuery
    {
        $this->parentSetType($value);
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
                $this->type === null &&
                $this->userCategory === null
            )
        ) {
            return;
        }

        $alias = $this->joinOrganizationUserTable($query->subQuery);

        $this->applyOrganizationParam(
            $query->subQuery,
            $this->organization,
            $alias
        );

        $this->applyUserCategoryParam(
            $query->subQuery,
            $this->userCategory
        );

        // todo - implement types
    }

    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @inheritdoc
     */
    protected function joinOrganizationUserTable(Query $query): string
    {
        $alias = OrganizationUsersRecord::tableAlias();

        $query->leftJoin(
            OrganizationUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.userId]] = [[elements.id]]'
        );

        return $alias;
    }

    /**
     * @inheritdoc
     */
    protected function joinOrganizationUserCollectionTable(Query $query): string
    {
        $alias = UserCollectionUsersRecord::tableAlias();
        $orgAlias = OrganizationUsersRecord::tableAlias();
        $query->leftJoin(
            UserCollectionUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.userId]] = [[' . $orgAlias . '.id]]'
        );

        return $alias;
    }


    /************************************************************
     * ORGANIZATION
     ************************************************************/

    /**
     * @param Query $query
     * @param $organization
     * @param string $alias
     */
    protected function applyOrganizationParam(Query $query, $organization, string $alias)
    {
        if (empty($organization)) {
            return;
        }

        $query->andWhere(
            Db::parseParam($alias . '.organizationId', $this->parseOrganizationValue($organization))
        );
    }


    /************************************************************
     * USER CATEGORY
     ************************************************************/

    /**
     * @param Query $query
     * @param $category
     */
    protected function applyUserCategoryParam(Query $query, $category)
    {
        if (empty($category)) {
            return;
        }

        $alias = $this->joinOrganizationUserCollectionTable($query);
        $query->andWhere(
            Db::parseParam($alias . '.categoryId', $this->parseUserCategoryValue($category))
        );

        $query->distinct(true);
    }
}
