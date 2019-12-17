<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\UserAttributeTrait;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserAssociation one($db = null)
 * @method UserAssociation[] all($db = null)
 * @method UserAssociation[] getCachedResult($db = null)
 */
class UserAssociationQuery extends CacheableActiveQuery
{
    use UserAttributeTrait,
        OrganizationAttributeTrait,
        UserTypeAttributeTrait;

    /**
     * @var string|string[]|null
     */
    public $state;

    /**
     * @param string|string[]|null $value
     * @return static The query object
     */
    public function setState($value)
    {
        $this->state = $value;
        return $this;
    }

    /**
     * @param string|string[]|null $value
     * @return static The query object
     */
    public function state($value)
    {
        return $this->setState($value);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->from([
            UserAssociation::tableName() . ' ' . UserAssociation::tableAlias()
        ]);

        parent::init();
    }

    /**
     * @return string
     */
    protected function joinUserTypeTable(): string
    {
        $name = UserTypeAssociation::tableName();
        $alias = UserTypeAssociation::tableAlias();

        $table = "{$name} {$alias}";

        if (!is_array($this->join) || !$this->isJoined($table)) {
            $this->leftJoin(
                $table,
                '[[' . $alias . '.userId]] = [['. UserAssociation::tableAlias() .'.id]]'
            );
        }

        return $alias;
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
        $attributes = ['state'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->{$attribute})) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }

        $this->applyUserParam();
        $this->applyOrganizationParam();
        $this->applyUserTypeParam();

        return parent::prepare($builder);
    }

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserParam()
    {
        // Is the query already doomed?
        if ($this->user !== null && empty($this->user)) {
            throw new QueryAbortedException();
        }

        if (empty($this->user)) {
            return;
        }

        $this->andWhere(
            Db::parseParam('userId', $this->parseUserValue($this->user))
        );
    }

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyOrganizationParam()
    {
        // Is the query already doomed?
        if ($this->organization !== null && empty($this->organization)) {
            throw new QueryAbortedException();
        }

        if (empty($this->organization)) {
            return;
        }

        $this->andWhere(
            Db::parseParam('organizationId', $this->parseOrganizationValue($this->organization))
        );
    }

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserTypeParam()
    {
        // Is the query already doomed?
        if ($this->userType !== null && empty($this->userType)) {
            throw new QueryAbortedException();
        }

        if (empty($this->userType)) {
            return;
        }

        $alias = $this->joinUserTypeTable();

        $this->andWhere(
            Db::parseParam($alias . '.typeId', $this->parseUserTypeValue($this->userType))
        );
    }
}
