<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users;

use flipbox\craft\ember\actions\records\UpdateRecord;
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UpdateUserType extends UpdateRecord
{
    /**
     * @inheritdoc
     */
    public $validBodyParams = [
        'name',
        'handle'
    ];

    /**
     * @inheritdoc
     */
    public function run($type)
    {
        return parent::run($type);
    }

    /**
     * @inheritdoc
     * @return UserType
     */
    protected function find($identifier)
    {
        return UserType::findOne($identifier);
    }
}
