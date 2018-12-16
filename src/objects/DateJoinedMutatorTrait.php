<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\objects;

use craft\helpers\DateTimeHelper;
use DateTime;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait DateJoinedMutatorTrait
{
    /**
     * @param $value
     * @return $this
     */
    public function setDateJoined($value)
    {
        if ($value) {
            $value = DateTimehelper::toDateTime($value);
        }

        $this->dateJoined = $value ?: null;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateJoined()
    {
        if (empty($this->dateJoined)) {
            return DateTimeHelper::toDateTime(
                new DateTime('now')
            );
        }

        return $this->dateJoined;
    }

    /**
     * @return string|null
     */
    public function getDateJoinedIso8601()
    {
        if (!$dateJoined = $this->getDateJoined()) {
            return null;
        }

        if (!$iso = DateTimeHelper::toIso8601($dateJoined)) {
            return null;
        }

        return $iso;
    }
}
