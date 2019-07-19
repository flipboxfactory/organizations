<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\models;

use Craft;
use DateTime;
use flipbox\organizations\objects\DateJoinedMutatorTrait;
use flipbox\organizations\Organizations;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait DateJoinedAttributeTrait
{
    use DateJoinedRulesTrait, DateJoinedMutatorTrait;

    /**
     * @var DateTime|null
     */
    private $dateJoined;

    /**
     * @return array
     */
    protected function dateJoinedAttributes(): array
    {
        return [
            'dateJoined'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function dateJoinedAttributeLabels()
    {
        return [
            'dateJoined' => Organizations::t('Date Joined')
        ];
    }
}
