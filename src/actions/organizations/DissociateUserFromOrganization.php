<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\records\DeleteRecordTrait;
use flipbox\organizations\records\UserAssociation;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class DissociateUserFromOrganization extends Action
{
    use DeleteRecordTrait, LookupAssociationTrait;

    /**
     * @inheritdoc
     * @param UserAssociation $record
     * @return bool
     */
    protected function performAction(UserAssociation $association): bool
    {
        return $association->save();
    }
}
