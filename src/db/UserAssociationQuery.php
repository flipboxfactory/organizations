<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\organizations\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserAssociation one($db = null)
 * @method UserAssociation[] all($db = null)
 * @method UserAssociation[] getCachedResult($db = null)
 */
class UserAssociationQuery extends SortableAssociationQuery
{
    /**
     * The sort order attribute
     */
    const SORT_ORDER_ATTRIBUTE = null;

    /**
     * @var int|int[]|false|null The source Id(s). Prefix Ids with "not " to exclude them.
     */
    public $userId;

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
                UserAssociation::tableName() . ' ' . UserAssociation::tableAlias()
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'id';
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
    public function user($value)
    {
        return $this->userId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function userId($value)
    {
        $this->userId = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function prepare($builder)
    {
        if ($this->userId !== null) {
            $this->andWhere(Db::parseParam('userId', $this->userId));
        }

        if ($this->organizationId !== null) {
            $this->andWhere(Db::parseParam('organizationId', $this->organizationId));
        }

        return parent::prepare($builder);
    }
}
