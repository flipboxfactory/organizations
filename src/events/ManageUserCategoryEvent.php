<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\events;

use craft\elements\User;
use craft\events\CancelableEvent;
use flipbox\organization\elements\Organization;
use flipbox\organization\records\UserCategory;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ManageUserCategoryEvent extends CancelableEvent
{
    /**
     * @var UserCategory
     */
    public $category;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Organization
     */
    public $organization;
}
