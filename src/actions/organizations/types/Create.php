<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations\types;

use flipbox\ember\actions\model\ModelCreate;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\OrganizationType;
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
     * @param OrganizationType $object
     * @return OrganizationType
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
     * @return OrganizationType|Model
     */
    protected function newModel(array $config = []): Model
    {
        return Organizations::getInstance()->getOrganizationTypes()->create($config);
    }

    /**
     * @inheritdoc
     * @param OrganizationType|Model $model
     */
    protected function performAction(Model $model): bool
    {
        return $model->insert();
    }
}
