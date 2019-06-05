<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users;

use craft\elements\User;
use flipbox\organizations\behaviors\OrganizationsAssociatedToUserBehavior;
use flipbox\organizations\elements\Organization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DissociateUserFromOrganization extends AbstractUserAssociation
{
    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function performAction(User $user, Organization $organization, int $sortOrder = null): bool
    {
        $query = Organization::find();
        $query->setCachedResult([$organization]);

        /** @var OrganizationsAssociatedToUserBehavior $user */
        return $user->getOrganizationManager()->dissociateMany($query);
    }
}
