<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\elements\ViewElement;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\elements\Organization as OrganizationElement;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ViewOrganization extends ViewElement
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
        return Organization::findOne([
            'id' => $id
        ]);
    }
}
