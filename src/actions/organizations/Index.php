<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\ember\actions\element\ElementIndex;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Index extends ElementIndex
{
    /**
     * @inheritdoc
     * @return OrganizationQuery
     */
    public function createQuery(array $config = []): QueryInterface
    {
        $query = Organization::find()
            ->status(null);

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }
}
