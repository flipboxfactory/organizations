<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\records\RecordIndex;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\records\OrganizationType;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationTypeIndex extends RecordIndex
{
    /**
     * @inheritdoc
     * @return OrganizationType
     */
    public function createQuery(array $config = []): QueryInterface
    {
        $query = OrganizationType::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }
}
