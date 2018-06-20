<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\organizations\records\UserTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserTypeAssociation one($db = null)
 * @method UserTypeAssociation[] all($db = null)
 * @method UserTypeAssociation[] getCachedResult($db = null)
 */
class UserTypeAssociationQuery extends SortableAssociationQuery
{
    /**
     * @var int|int[]|false|null The user Id(s). Prefix Ids with "not " to exclude them.
     */
    public $userId;

    /**
     * @var int|int[]|false|null The type Id(s). Prefix Ids with "not " to exclude them.
     */
    public $typeId;

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'typeId';
    }

    /**
     * @inheritdoc
     * return static
     */
    public function type($value)
    {
        return $this->typeId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function typeId($value)
    {
        $this->typeId = $value;
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

        if ($this->typeId !== null) {
            $this->andWhere(Db::parseParam('typeId', $this->typeId));
        }

        return parent::prepare($builder);
    }
}
