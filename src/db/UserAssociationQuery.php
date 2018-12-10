<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\helpers\Db;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\UserAttributeTrait;
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
class UserAssociationQuery extends CacheableActiveQuery
{
    use UserAttributeTrait;

    /**
     * @var int|int[]|false|null The target Id(s). Prefix Ids with "not " to exclude them.
     */
    public $organizationId;

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
     */
    public function prepare($builder)
    {
        if ($this->user !== null) {
            $this->andWhere(Db::parseParam('userId', $this->parseUserValue($this->user)));
        }

        if ($this->organizationId !== null) {
            $this->andWhere(Db::parseParam('organizationId', $this->organizationId));
        }

        return parent::prepare($builder);
    }
}
