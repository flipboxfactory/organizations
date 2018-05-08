<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationUserAssociationQuery extends UserAssociationQuery
{
    /**
     * The sort order attribute
     */
    const SORT_ORDER_ATTRIBUTE = 'organizationOrder';

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'organizationId';
    }
}
