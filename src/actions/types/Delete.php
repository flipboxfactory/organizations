<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\types;

use flipbox\ember\actions\model\ModelDelete;
use flipbox\organization\records\Type;
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
    public function run($type)
    {
        return parent::run($type);
    }

    /**
     * @inheritdoc
     * @param Type|Model $model
     * @throws \Exception
     */
    protected function performAction(Model $model): bool
    {
        return $model->delete();
    }
}
