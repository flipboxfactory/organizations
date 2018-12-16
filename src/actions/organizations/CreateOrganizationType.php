<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\records\CreateRecord;
use flipbox\organizations\records\OrganizationType;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CreateOrganizationType extends CreateRecord
{
    use PopulateOrganizationTypeTrait;

    /**
     * @inheritdoc
     * @param OrganizationType $record
     * @return OrganizationType
     */
    protected function populate(ActiveRecord $record): ActiveRecord
    {
        $this->populateSiteLayout($record);
        $this->populateSiteSettings($record);

        return parent::populate($record);
    }

    /**
     * @inheritdoc
     * @return OrganizationType
     */
    protected function newRecord(array $config = []): ActiveRecord
    {
        return new OrganizationType($config);
    }
}
