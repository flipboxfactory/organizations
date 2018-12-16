<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\elements\UpdateElement;
use flipbox\organizations\elements\Organization;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UpdateOrganization extends UpdateElement
{
    use PopulateOrganizationTrait;

    /**
     * @inheritdoc
     */
    public function run($organization)
    {
        return parent::run($organization);
    }

    /**
     * @inheritdoc
     * @return Organization
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
     * @param Organization $object
     * @return Organization
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     */
    protected function populate(BaseObject $object): BaseObject
    {
        parent::populate($object);
        $this->populateFromRequest($object);

        return $object;
    }
}
