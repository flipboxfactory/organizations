<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\users\categories;

use flipbox\ember\actions\model\ModelCreate;
use flipbox\organization\Organizations;
use flipbox\organization\records\UserCategory;
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
     * @return UserCategory
     */
    protected function newModel(array $config = []): Model
    {
        return Organizations::getInstance()->getUserCategories()->create($config);
    }

    /**
     * @inheritdoc
     * @param UserCategory $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->insert();
    }
}
