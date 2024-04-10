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
     * @param $value
     * @return $this
     */
    public function type($value)
    {
        return $this->typeId($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function typeId($value)
    {
        $this->typeId = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTypeId($value)
    {
        return $this->typeId($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function user($value)
    {
        return $this->userId($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function userId($value)
    {
        $this->userId = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUserId($value)
    {
        return $this->userId($value);
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
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

        if (is_null($this->userId)) {
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

        if (is_null($this->typeId)) {
            return;
        }

        $this->andWhere(
            Db::parseParam('typeId', $this->typeId)
        );
    }
}
