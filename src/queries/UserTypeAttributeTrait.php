<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\Query;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserTypeAttributeTrait
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
     * @return array|string
     */
    protected function parseUserTypeValue($value)
    {
        return QueryHelper::prepareParam(
            $value,
            function (string $handle) {
                $value = (new Query())
                    ->select(['id'])
                    ->from([UserType::tableName()])
                    ->where(['handle' => $handle])
                    ->scalar();
                return empty($value) ? false : $value;
            }
        );
    }
}
