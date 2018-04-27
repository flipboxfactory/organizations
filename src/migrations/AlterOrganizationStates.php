<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\migrations;

use craft\db\Migration;
use flipbox\organizations\records\Organization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AlterOrganizationStates extends Migration
{
    /**
     * The state column name
     */
    const COLUMN_NAME = 'state';

    /**
     * @var array|null
     */
    public $states;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $type = $this->string();

        if (!empty($this->states)) {
            $type = $this->enum(self::COLUMN_NAME, (array)$this->states);
        }

        $this->alterColumn(
            Organization::tableName(),
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
