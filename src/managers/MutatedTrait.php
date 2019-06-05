<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use DateTime;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait MutatedTrait
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
