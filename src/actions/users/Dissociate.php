<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\users;

use flipbox\organization\Organizations as OrganizationPlugin;
use flipbox\organization\records\UserAssociation;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Dissociate extends Action
{
    /**
     * @inheritdoc
     * @param UserAssociation $model
     */
    protected function performAction(Model $model): bool
    {
        if (true === $this->ensureUserAssociation($model)) {
            return OrganizationPlugin::getInstance()->getUserAssociations()->dissociate(
                $model
            );
        }

        return false;
    }
}
