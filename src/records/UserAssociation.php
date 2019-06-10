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
use flipbox\organizations\managers\RelationshipManagerInterface;
use flipbox\organizations\managers\UserTypeRelationshipManager;
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
 * @property UserType[] $types
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
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['userId', 'organizationId'];

    /**
     * @var RelationshipManagerInterface
     */
    private $manager;

    /**
     * @return RelationshipManagerInterface
     */
    public function getTypeManager(): RelationshipManagerInterface
    {
        if (null === $this->manager) {
            $this->manager = new UserTypeRelationshipManager($this);
        }

        return $this->manager;
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
    public function getTypes(): ActiveQueryInterface
    {
        // Todo - order this by the sortOrder
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany(UserType::class, ['id' => 'typeId'])
            ->viaTable(
                UserTypeAssociation::tableName(),
                ['userId' => 'id']
            );
    }
}
