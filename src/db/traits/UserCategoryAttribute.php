<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db\traits;

use craft\db\Query;
use craft\helpers\Db;
use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organizations\records\UserCategory;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserCategoryAttribute
{
    /**
     * The user category(s) that the resulting organizations’ users must be in.
     *
     * @var string|string[]|int|int[]|UserCategory|UserCategory[]|null
     */
    public $userCategory;

    /**
     * @param string|string[]|int|int[]|UserCategory|UserCategory[]|null $value
     * @return static The query object
     */
    public function setUserCategory($value)
    {
        $this->userCategory = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|UserCategory|UserCategory[]|null $value
     * @return static The query object
     */
    public function userCategory($value)
    {
        return $this->setUserCategory($value);
    }

    /**
     * @param string|string[]|int|int[]|UserCategory|UserCategory[]|null $value
     * @return static The query object
     */
    public function setUserCategoryId($value)
    {
        return $this->setUserCategory($value);
    }

    /**
     * @param string|string[]|int|int[]|UserCategory|UserCategory[]|null $value
     * @return static The query object
     */
    public function userCategoryId($value)
    {
        return $this->setUserCategory($value);
    }

    /**
     * @param $value
     * @param string $join
     * @return array
     */
    protected function parseUserCategoryValue($value, string $join = 'and'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveUserCategoryValue($operator, $v);
            }
        }

        $value = ArrayHelper::filterEmptyAndNullValuesFromArray($value);

        if (empty($value)) {
            return [];
        }

        // parse param to allow for mixed variables
        return array_merge([$join], $value);
    }

    /**
     * @param $operator
     * @param $value
     */
    protected function resolveUserCategoryValue($operator, &$value)
    {
        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                $value = $this->resolveUserCategoryStringValue($value);
            }

            if ($value instanceof UserCategory) {
                $value = $value->id;
            }

            if ($value) {
                $value = QueryHelper::assembleParamValue($value, $operator);
            }
        }
    }

    /**
     * @param string $value
     * @return string|false
     */
    protected function resolveUserCategoryStringValue(string $value)
    {
        $value = (new Query())
            ->select(['id'])
            ->from([UserCategory::tableName()])
            ->where(Db::parseParam('handle', $value))
            ->scalar();
        return empty($value) ? false : $value;
    }
}
