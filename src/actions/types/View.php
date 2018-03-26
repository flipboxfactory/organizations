<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\types;

use flipbox\ember\actions\model\ModelView;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\Type;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class View extends ModelView
{
    /**
     * @inheritdoc
     */
    public function run($type)
    {
        return parent::run($type);
    }

    /**
     * @inheritdoc
     * @return Type
     */
    protected function find($identifier)
    {
        return Organizations::getInstance()->getTypes()->get($identifier);
    }
}
