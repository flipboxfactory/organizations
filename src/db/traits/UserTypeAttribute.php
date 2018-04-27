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
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserTypeAttribute
{
    /**
     * The user type(s) that the resulting organizations’ users must be in.
     *
     * @var string|string[]|int|int[]|UserType|UserType[]|null
     */
    public $userType;

    /**
     * @param string|string[]|int|int[]|UserType|UserType[]|null $value
     * @return static The query object
     */
    public function setUserType($value)
    {
        $this->userType = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|UserType|UserType[]|null $value
     * @return static The query object
     */
    public function userType($value)
    {
        return $this->setUserType($value);
    }

    /**
     * @param string|string[]|int|int[]|UserType|UserType[]|null $value
     * @return static The query object
     */
    public function setUserTypeId($value)
    {
        return $this->setUserType($value);
    }

    /**
     * @param string|string[]|int|int[]|UserType|UserType[]|null $value
     * @return static The query object
     */
    public function userTypeId($value)
    {
        return $this->setUserType($value);
    }

    /**
     * @param $value
     * @param string $join
     * @return array
     */
    protected function parseUserTypeValue($value, string $join = 'and'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveUserTypeValue($operator, $v);
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
    protected function resolveUserTypeValue($operator, &$value)
    {
        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                $value = $this->resolveUserTypeStringValue($value);
            }

            if ($value instanceof UserType) {
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
    protected function resolveUserTypeStringValue(string $value)
    {
        $value = (new Query())
            ->select(['id'])
            ->from([UserType::tableName()])
            ->where(Db::parseParam('handle', $value))
            ->scalar();
        return empty($value) ? false : $value;
    }
}
