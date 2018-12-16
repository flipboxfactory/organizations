<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users;

use flipbox\organizations\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AssociateUserToOrganization extends AbstractUserAssociation
{
    /**
     * @inheritdoc
     */
    protected function performAction(UserAssociation $record): bool
    {
        return $record->save();
    }
}
