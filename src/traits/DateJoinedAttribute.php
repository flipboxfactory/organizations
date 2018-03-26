<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\traits;

use Craft;
use DateTime;

/**
 * @property DateTime|null $dateJoined
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait DateJoinedAttribute
{
    use DateJoinedRules, DateJoinedMutator;

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
    public function dateJoinedAttributeLabels()
    {
        return [
            'dateJoined' => Craft::t('organizations', 'Date Created')
        ];
    }
}
