<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\events;

use craft\events\CancelableEvent;
use flipbox\organization\elements\Organization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ChangeStateEvent extends CancelableEvent
{
    /**
     * @var Organization
     */
    public $organization;

    /**
     * The new organization state
     *
     * @var string
     */
    public $to;
}
