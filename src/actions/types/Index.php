<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\types;

use flipbox\ember\actions\model\ModelIndex;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\Organizations;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Index extends ModelIndex
{
    /**
     * @inheritdoc
     * @return OrganizationQuery
     */
    public function createQuery(array $config = []): QueryInterface
    {
        return Organizations::getInstance()->getTypes()->getQuery($config);
    }
}
