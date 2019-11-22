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
 * This migration alters the 'state' column to include the new 'invited' option.
 */
class m191118_181324_user_state_column extends Migration
{
    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = $this->getDb()->getSchema()->getTableSchema(
            UserAssociation::tableName()
        );

        $states = array_keys(Organizations::getInstance()->getSettings()->getUserStates());
        $defaultState = Organizations::getInstance()->getSettings()->getDefaultUserState();

        $type = $this->enum(
            AlterUserStates::COLUMN_NAME,
            $states
        )->defaultValue($defaultState)->notNull();

        $this->alterColumn(
            UserAssociation::tableName(),
            AlterUserStates::COLUMN_NAME,
            $type
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191118_1813249_user_state_column cannot be reverted.\n";
        return true;
    }
}
