<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\users\categories;

use flipbox\ember\actions\model\ModelDelete;
use flipbox\organization\records\UserCategory;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Delete extends ModelDelete
{
    use traits\Lookup;

    /**
     * @inheritdoc
     */
    public function run($category)
    {
        return parent::run($category);
    }

    /**
     * @inheritdoc
     * @param UserCategory $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->delete();
    }
}
