<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\organizations;

use flipbox\ember\actions\element\ElementDelete;
use flipbox\organization\elements\Organization as OrganizationElement;
use flipbox\organization\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Delete extends ElementDelete
{
    /**
     * @inheritdoc
     */
    public function run($organization)
    {
        return parent::run($organization);
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    public function find($identifier)
    {
        return Organizations::getInstance()->getOrganizations()->find($identifier);
    }
}
