<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\migrations;

use craft\db\Migration;
use craft\records\Element as ElementRecord;
use craft\records\FieldLayout as FieldLayoutRecord;
use craft\records\Site as SiteRecord;
use craft\records\User as UserRecord;
use flipbox\organization\records\Organization as OrganizationRecord;
use flipbox\organization\records\Type as OrganizationTypeRecord;
use flipbox\organization\records\TypeAssociation as OrganizationTypeAssociationRecord;
use flipbox\organization\records\TypeSiteSettings as OrganizationTypeSiteSettingsRecord;
use flipbox\organization\records\UserAssociation as OrganizationUserRecord;
use flipbox\organization\records\UserCategory as OrganizationUserCategoryRecord;
use flipbox\organization\records\UserCategoryAssociation as OrganizationUserCategoryAssociationRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(OrganizationUserCategoryAssociationRecord::tableName());
        $this->dropTableIfExists(OrganizationUserCategoryRecord::tableName());
        $this->dropTableIfExists(OrganizationUserRecord::tableName());
        $this->dropTableIfExists(OrganizationTypeAssociationRecord::tableName());
        $this->dropTableIfExists(OrganizationTypeSiteSettingsRecord::tableName());
        $this->dropTableIfExists(OrganizationTypeRecord::tableName());
        $this->dropTableIfExists(OrganizationRecord::tableName());

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(OrganizationRecord::tableName(), [
            'id' => $this->primaryKey(),
            'state' => $this->string(),
            'dateJoined' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationTypeRecord::tableName(), [
            'id' => $this->primaryKey(),
            'handle' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'fieldLayoutId' => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationTypeSiteSettingsRecord::tableName(), [
            'typeId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'enabledByDefault' => $this->boolean()->defaultValue(true)->notNull(),
            'hasUrls' => $this->boolean()->defaultValue(true)->notNull(),
            'uriFormat' => $this->text(),
            'template' => $this->string(500),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationTypeAssociationRecord::tableName(), [
            'typeId' => $this->integer()->notNull(),
            'organizationId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationUserRecord::tableName(), [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'organizationId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationUserCategoryRecord::tableName(), [
            'id' => $this->primaryKey(),
            'handle' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(OrganizationUserCategoryAssociationRecord::tableName(), [
            'userId' => $this->integer()->notNull(),
            'categoryId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeRecord::tableName(),
                'handle',
                true
            ),
            OrganizationTypeRecord::tableName(),
            'handle',
            true
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeRecord::tableName(),
                'fieldLayoutId',
                false,
                true
            ),
            OrganizationTypeRecord::tableName(),
            'fieldLayoutId',
            false
        );

        $this->addPrimaryKey(
            $this->db->getPrimaryKeyName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                ['typeId', 'siteId']
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            ['typeId', 'siteId']
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                'typeId',
                false,
                true
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            'typeId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                'siteId',
                false,
                true
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            'siteId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                'typeId,siteId',
                true
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            'typeId,siteId',
            true
        );

        $this->addPrimaryKey(
            $this->db->getPrimaryKeyName(
                OrganizationTypeAssociationRecord::tableName(),
                ['typeId', 'organizationId']
            ),
            OrganizationTypeAssociationRecord::tableName(),
            ['typeId', 'organizationId']
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeAssociationRecord::tableName(),
                'typeId',
                false,
                true
            ),
            OrganizationTypeAssociationRecord::tableName(),
            'typeId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeAssociationRecord::tableName(),
                'organizationId',
                false,
                true
            ),
            OrganizationTypeAssociationRecord::tableName(),
            'organizationId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationTypeAssociationRecord::tableName(),
                'typeId,organizationId',
                true
            ),
            OrganizationTypeAssociationRecord::tableName(),
            'typeId,organizationId',
            true
        );

        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserRecord::tableName(),
                'userId',
                false,
                true
            ),
            OrganizationUserRecord::tableName(),
            'userId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserRecord::tableName(),
                'organizationId',
                false,
                true
            ),
            OrganizationUserRecord::tableName(),
            'organizationId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserRecord::tableName(),
                'userId,organizationId',
                true
            ),
            OrganizationUserRecord::tableName(),
            'userId,organizationId',
            true
        );

        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserCategoryRecord::tableName(),
                'handle',
                true
            ),
            OrganizationUserCategoryRecord::tableName(),
            'handle',
            true
        );

        $this->addPrimaryKey(
            $this->db->getPrimaryKeyName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                ['userId', 'categoryId']
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            ['userId', 'categoryId']
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                'userId',
                false,
                true
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            'userId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                'categoryId',
                false,
                true
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            'categoryId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                'userId,categoryId',
                true
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            'userId,categoryId',
            true
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationRecord::tableName(),
                'id'
            ),
            OrganizationRecord::tableName(),
            'id',
            ElementRecord::tableName(),
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationTypeRecord::tableName(),
                'fieldLayoutId'
            ),
            OrganizationTypeRecord::tableName(),
            'fieldLayoutId',
            FieldLayoutRecord::tableName(),
            'id',
            'SET NULL',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                'typeId'
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            'typeId',
            OrganizationTypeRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationTypeSiteSettingsRecord::tableName(),
                'siteId'
            ),
            OrganizationTypeSiteSettingsRecord::tableName(),
            'siteId',
            SiteRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationTypeAssociationRecord::tableName(),
                'typeId'
            ),
            OrganizationTypeAssociationRecord::tableName(),
            'typeId',
            OrganizationTypeRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationTypeAssociationRecord::tableName(),
                'organizationId'
            ),
            OrganizationTypeAssociationRecord::tableName(),
            'organizationId',
            OrganizationRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationUserRecord::tableName(),
                'userId'
            ),
            OrganizationUserRecord::tableName(),
            'userId',
            UserRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationUserRecord::tableName(),
                'organizationId'
            ),
            OrganizationUserRecord::tableName(),
            'organizationId',
            OrganizationRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                'categoryId'
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            'categoryId',
            OrganizationUserCategoryRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                OrganizationUserCategoryAssociationRecord::tableName(),
                'userId'
            ),
            OrganizationUserCategoryAssociationRecord::tableName(),
            'userId',
            OrganizationUserRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
