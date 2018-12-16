<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\records\UpdateRecord;
use flipbox\organizations\records\OrganizationType;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UpdateOrganizationType extends UpdateRecord
{
    use PopulateOrganizationTypeTrait;

    /**
     * @inheritdoc
     */
    public function run($type)
    {
        return parent::run($type);
    }

    /**
     * @inheritdoc
     * @param OrganizationType $record
     * @return OrganizationType
     */
    protected function populate(ActiveRecord $record): ActiveRecord
    {
        parent::populate($record);
        $this->populateSiteLayout($record);
        $this->populateSiteSettings($record);

        return $record;
    }

    /**
     * @inheritdoc
     * @return OrganizationType|null
     */
    protected function find($identifier)
    {
        return OrganizationType::findOne($identifier);
    }
}
