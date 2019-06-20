<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\IdAttributeTrait;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\craft\ember\records\UserAttributeTrait;
use flipbox\organizations\relationships\RelationshipInterface;
use flipbox\organizations\relationships\UserTypeRelationship;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserAssociationQuery;
use yii\db\ActiveQueryInterface;
use yii\helpers\Json;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $organizationId
 * @property int $organizationOrder The order which an organization lists its users
 * @property int $userOrder The order which a user lists its organizations
 * @property string $state The user state
 * @property Organization $organization
 * @property UserType[] $typeRecords
 */
class UserAssociation extends ActiveRecord
{
    const STATE_ACTIVE = 'active';
    const STATE_PENDING = 'pending';
    const STATE_INACTIVE = 'inactive';

    use SortableTrait,
        UserAttributeTrait,
        IdAttributeTrait,
        OrganizationAttributeTrait;

    /**
     * The table name
     */
    const TABLE_ALIAS = Organization::TABLE_ALIAS . '_user_associations';

    /**
     * Whether associated types should be saved
     *
     * @var bool
     */
    private $saveTypes = true;

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['userId', 'organizationId'];

    /**
     * @var RelationshipInterface
     */
    private $manager;

    /**
     * @return static
     */
    public function withTypes(): self
    {
        $this->saveTypes = true;
        return $this;
    }

    /**
     * @return static
     */
    public function withoutTypes(): self
    {
        $this->saveTypes = false;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Default state
        if (empty($this->state)) {
            $this->state = Organizations::getInstance()->getSettings()->getDefaultUserState();
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @inheritdoc
     * @return UserAssociationQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(UserAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->idRules(),
            $this->userRules(),
            $this->organizationRules(),
            [
                [
                    [
                        'userId',
                        'organizationId',
                        'state'
                    ],
                    'required'
                ],
                [
                    [
                        'state'
                    ],
                    'in',
                    'range' => array_keys(Organizations::getInstance()->getSettings()->getUserStates())
                ],
                [
                    [
                        'state'
                    ],
                    'default',
                    'value' => Organizations::getInstance()->getSettings()->getDefaultUserState()
                ],
                [
                    [
                        'userOrder',
                        'organizationOrder'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'userId',
                        'organizationId'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (Organizations::getInstance()->getSettings()->getEnforceUserSortOrder()) {
            $this->ensureSortOrder(
                [
                    'userId' => $this->userId
                ],
                'organizationOrder'
            );
        }

        if (Organizations::getInstance()->getSettings()->getEnforceOrganizationSortOrder()) {
            $this->ensureSortOrder(
                [
                    'organizationId' => $this->organizationId
                ],
                'userOrder'
            );
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        try {
            if (Organizations::getInstance()->getSettings()->getEnforceUserSortOrder()) {
                $this->autoReOrder(
                    'userId',
                    [
                        'organizationId' => $this->organizationId
                    ],
                    'userOrder'
                );
            }

            if (Organizations::getInstance()->getSettings()->getEnforceOrganizationSortOrder()) {
                $this->autoReOrder(
                    'organizationId',
                    [
                        'userId' => $this->userId
                    ],
                    'organizationOrder'
                );
            }
        } catch (\Exception $e) {
            Organizations::error(
                sprintf(
                    "Exception caught while trying to reorder '%s'. Exception: [%s].",
                    (string)get_class($this),
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );
        }

        // Save types if they've also been altered
        if (true === $this->saveTypes && $this->getTypes()->isMutated()) {
            $this->getTypes()->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        if (Organizations::getInstance()->getSettings()->getEnforceOrganizationSortOrder()) {
            $this->sequentialOrder(
                'organizationId',
                [
                    'userId' => $this->userId
                ],
                'organizationOrder'
            );
        }

        if (Organizations::getInstance()->getSettings()->getEnforceUserSortOrder()) {
            $this->sequentialOrder(
                'userId',
                [
                    'organizationId' => $this->organizationId
                ],
                'userOrder'
            );
        }

        parent::afterDelete();
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getTypeRecords(): ActiveQueryInterface
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany(UserType::class, ['id' => 'typeId'])
            ->viaTable(
                UserTypeAssociation::tableName(),
                ['userId' => 'id']
            );
    }

    /**
     * @return UserTypeRelationship|RelationshipInterface
     */
    public function getTypes(): RelationshipInterface
    {
        if (null === $this->manager) {
            $this->manager = new UserTypeRelationship($this);
        }

        return $this->manager;
    }


    /************************************************************
     * RELATIONS
     ************************************************************/

    /**
     * We're using an alias so 'types' can be used to retrieve relations
     *
     * @inheritDoc
     */
    public function getRelation($name, $throwException = true)
    {
        if ($name === 'types') {
            $name = 'typeRecords';
        }

        return parent::getRelation($name);
    }

    /**
     * We're using an alias so 'types' is converted to 'typeRecords'
     *
     * @inheritDoc
     */
    public function populateRelation($name, $records)
    {
        if ($name === 'types') {
            $name = 'typeRecords';
        }

        parent::populateRelation($name, $records);
    }
}
