<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users;

use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserAssociation;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Associate extends Action
{
    /**
     * @inheritdoc
     * @param UserAssociation $model
     */
    protected function performAction(Model $model): bool
    {
        if (true === $this->ensureUserAssociation($model)) {
            return OrganizationPlugin::getInstance()->getUserAssociations()->associate(
                $model
            );
        }

        return false;
    }
}
