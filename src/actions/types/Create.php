<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\types;

use flipbox\ember\actions\model\ModelCreate;
use flipbox\organization\Organization;
use flipbox\organization\records\Type;
use yii\base\BaseObject;
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
     * @param Type $object
     * @return Type
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
     * @return Type|Model
     */
    protected function newModel(array $config = []): Model
    {
        return Organization::getInstance()->getTypes()->create($config);
    }

    /**
     * @inheritdoc
     * @param Type|Model $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->insert();
    }
}
