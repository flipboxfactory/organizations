<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\queries\ActiveQuery;
use flipbox\organizations\records\UserTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserTypeAssociation one($db = null)
 * @method UserTypeAssociation[] all($db = null)
 * @method UserTypeAssociation[] getCachedResult($db = null)
 */
class UserTypeAssociationQuery extends ActiveQuery
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
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
        // Is the query already doomed?
        if ($this->userId !== null && empty($this->userId)) {
            throw new QueryAbortedException();
        }

        $this->applyUserParam();
        $this->applyTypeParam();

        return parent::prepare($builder);
    }

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyUserParam()
    {
        // Is the query already doomed?
        if ($this->userId !== null && empty($this->userId)) {
            throw new QueryAbortedException();
        }

        if (empty($this->userId)) {
            return;
        }

        $this->andWhere(
            Db::parseParam('userId', $this->userId)
        );
    }

    /**
     * @return void
     * @throws QueryAbortedException
     */
    protected function applyTypeParam()
    {
        // Is the query already doomed?
        if ($this->typeId !== null && empty($this->typeId)) {
            throw new QueryAbortedException();
        }

        if (empty($this->typeId)) {
            return;
        }

        $this->andWhere(
            Db::parseParam('typeId', $this->typeId)
        );
    }
}
