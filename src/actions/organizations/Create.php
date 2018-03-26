<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\organizations;

use craft\base\ElementInterface;
use flipbox\ember\actions\element\ElementCreate;
use flipbox\organization\elements\Organization as OrganizationElement;
use flipbox\organization\Organizations;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Create extends ElementCreate
{
    use traits\Populate;

    /**
     * @inheritdoc
     * @return OrganizationElement|ElementInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function newElement(array $config = []): ElementInterface
    {
        return Organizations::getInstance()->getOrganizations()->create($config);
    }

    /**
     * @inheritdoc
     * @param OrganizationElement $object
     * @return OrganizationElement
     */
    public function populate(BaseObject $object): BaseObject
    {
        if (true === $this->ensureOrganization($object)) {
            parent::populate($object);
            $this->populateFromRequest($object);
        }

        return $object;
    }
}
