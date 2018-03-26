<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\ember\actions\element\ElementView;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class View extends ElementView
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
    protected function findById(int $id)
    {
        return Organizations::getInstance()->getOrganizations()->get($id);
    }
}
