<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\migrations;

use craft\db\Migration;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AlterUserStates extends Migration
{
    /**
     * The state column name
     */
    const COLUMN_NAME = 'state';

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function safeUp()
    {
        $states = array_keys(Organizations::getInstance()->getSettings()->getUserStates());
        $defaultState = Organizations::getInstance()->getSettings()->getDefaultUserState();

        $type = $this->enum(
            self::COLUMN_NAME,
            $states
        )->defaultValue($defaultState)->notNull();

        $this->alterColumn(
            UserAssociation::tableName(),
            self::COLUMN_NAME,
            $type
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
