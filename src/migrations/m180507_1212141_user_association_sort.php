<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\migrations;

use craft\db\Migration;
use flipbox\organizations\records\UserAssociation;

/**
 * This migration adds a new column to support multiple user association sort types.
 */
class m180507_1212141_user_association_sort extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->renameColumn(
            UserAssociation::tableName(),
            'sortOrder',
            'organizationOrder'
        );

        $this->addColumn(
            UserAssociation::tableName(),
            'userOrder',
            $this->smallInteger()->unsigned()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180507_1212141_user_association_sort cannot be reverted.\n";
        return false;
    }
}
