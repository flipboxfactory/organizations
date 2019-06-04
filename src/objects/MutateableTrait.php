<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use DateTime;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
trait MutateableTrait
{
    /**
     * @var bool
     */
    protected $mutated = false;

    /**
     * @return bool
     */
    public function isMutated(): bool
    {
        return $this->mutated;
    }
}
