<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

use craft\helpers\Db;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\organizations\records\UserCategoryAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserCategoryAssociation one($db = null)
 * @method UserCategoryAssociation[] all($db = null)
 * @method UserCategoryAssociation[] getCachedResult($db = null)
 */
class UserCategoryAssociationQuery extends SortableAssociationQuery
{
    /**
     * @var int|int[]|false|null The user Id(s). Prefix Ids with "not " to exclude them.
     */
    public $userId;

    /**
     * @var int|int[]|false|null The category Id(s). Prefix Ids with "not " to exclude them.
     */
    public $categoryId;

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'categoryId';
    }

    /**
     * @inheritdoc
     * return static
     */
    public function category($value)
    {
        return $this->categoryId($value);
    }

    /**
     * @inheritdoc
     * return static
     */
    public function categoryId($value)
    {
        $this->categoryId = $value;
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
     * Apply conditions
     */
    protected function applyConditions()
    {
        if ($this->userId !== null) {
            $this->andWhere(Db::parseParam('userId', $this->userId));
        }

        if ($this->categoryId !== null) {
            $this->andWhere(Db::parseParam('categoryId', $this->categoryId));
        }
    }
}
