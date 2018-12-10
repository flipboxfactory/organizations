<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\users\types;

use flipbox\craft\ember\actions\records\CreateRecord;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserType;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Create extends CreateRecord
{
    use traits\Populate;

    /**
     * @inheritdoc
     * @return UserType
     */
    protected function newRecord(array $config = []): ActiveRecord
    {
        $record = new UserType();

        $record->setAttributes($config);

        return $record;
    }
}
