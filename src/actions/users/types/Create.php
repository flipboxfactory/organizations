<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users\types;

use flipbox\ember\actions\model\ModelCreate;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserType;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Create extends ModelCreate
{
    use traits\Populate;

    /**
     * @inheritdoc
     * @return UserType
     */
    protected function newModel(array $config = []): Model
    {
        return Organizations::getInstance()->getUserTypes()->create($config);
    }

    /**
     * @inheritdoc
     * @param UserType $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->insert();
    }
}
