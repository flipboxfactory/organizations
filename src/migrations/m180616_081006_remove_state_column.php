<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\migrations;

use craft\db\Migration;
use flipbox\organizations\records\Organization as OrganizationRecord;

/**
 * This migration removes the 'state' column from elements record.
 */
class m180616_081006_remove_state_column extends Migration
{
    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = $this->getDb()->getSchema()->getTableSchema(
            OrganizationRecord::tableName()
        );

        if (isset($table->columns['state'])) {
            $this->dropColumn(OrganizationRecord::tableName(), 'state');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180616_081006_remove_state_column cannot be reverted.\n";
        return true;
    }
}
