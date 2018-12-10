<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\ember\actions\element\ElementUpdate;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Update extends ElementUpdate
{
    use traits\Populate;

    /**
     * @inheritdoc
     */
    public function run($organization)
    {
        return parent::run($organization);
    }

    /**
     * @inheritdoc
     * @return OrganizationElement
     */
    public function find($identifier)
    {
        $site = $this->resolveSiteFromRequest();
        return Organization::findOne([
            is_numeric($identifier) ? 'id' : 'slug' => $identifier,
            'siteId' => $site ? $site->id : null
        ]);
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
