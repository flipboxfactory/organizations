<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\elements\DeleteElement;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\elements\Organization as OrganizationElement;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DeleteOrganization extends DeleteElement
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
        return Organization::findOne([
            (is_numeric($identifier) ? 'id' : 'slug') => $identifier,
            'status' => null
        ]);
    }
}
