<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\types;

use flipbox\ember\actions\model\ModelUpdate;
use flipbox\organizations\records\Type;
use yii\base\BaseObject;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Update extends ModelUpdate
{
    use traits\Lookup, traits\Populate;

    /**
     * @inheritdoc
     */
    public function run($type)
    {
        return parent::run($type);
    }

    /**
     * @inheritdoc
     * @param Type|BaseObject $object
     * @return Type|BaseObject
     */
    protected function populate(BaseObject $object): BaseObject
    {
        if (true === $this->ensureType($object)) {
            parent::populate($object);
            $this->populateSiteLayout($object);
            $this->populateSiteSettings($object);
        }
        return $object;
    }

    /**
     * @inheritdoc
     * @param Type|Model $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->update();
    }
}
