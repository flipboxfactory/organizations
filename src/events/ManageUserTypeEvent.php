<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\events;

use craft\elements\User;
use craft\events\CancelableEvent;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ManageUserTypeEvent extends CancelableEvent
{
    /**
     * @var UserType
     */
    public $type;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Organization
     */
    public $organization;
}
