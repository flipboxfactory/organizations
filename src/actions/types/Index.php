<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\actions\types;

use flipbox\ember\actions\model\ModelIndex;
use flipbox\organization\db\OrganizationQuery;
use flipbox\organization\Organization;
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
        return Organization::getInstance()->getTypes()->getQuery($config);
    }
}
