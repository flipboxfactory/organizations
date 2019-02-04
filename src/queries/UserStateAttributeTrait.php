<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UserStateAttributeTrait
{
    /**
     * The user state(s) that the resulting organizations’ users must be in.
     *
     * @var string|string[]|null
     */
    public $userState;

    /**
     * @param string|string[]|int|int[]|null $value
     * @return static The query object
     */
    public function setUserState($value)
    {
        $this->userState = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|null $value
     * @return static The query object
     */
    public function userState($value)
    {
        return $this->setUserState($value);
    }
}
