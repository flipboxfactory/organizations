<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use craft\base\ElementInterface;
use craft\helpers\Db;
use craft\records\Element as ElementRecord;
use craft\records\User as UserRecord;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\OrganizationTypeAssociation as OrganizationTypeRecord;
use flipbox\organizations\records\UserAssociation as OrganizationUserRecord;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $state
 * @property string $dateJoined
 * @property ElementInterface $element
 * @property OrganizationType[] $types
 * @property UserRecord[] $users
 */
class Organization extends ActiveRecordWithId
{
    /**
     * The table name
     */
    const TABLE_ALIAS = 'organizations';

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            if (!$this->dateJoined) {
                $this->dateJoined = Db::prepareDateForDb(new \DateTime());
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array_merge(
            parent::rules()
        );

        $settings = OrganizationPlugin::getInstance()->getSettings();
        if ($settings->hasStates()) {
            $rules[] = [
                [
                    'state'
                ],
                'in',
                'range' => array_keys($settings->getStates())
            ];
        }

        return $rules;
    }

    /**
     * Returns the organizations's element.
     *
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(ElementRecord::class, ['id' => 'id']);
    }

    /**
     * Returns the organization's types.
     *
     * @return ActiveQueryInterface
     */
    public function getTypes(): ActiveQueryInterface
    {
        // Todo - apply order by
        return $this->hasMany(OrganizationType::class, ['id' => 'typeId'])
            ->viaTable(
                OrganizationTypeRecord::tableName(),
                ['organizationId' => 'id']
            );
    }

    /**
     * Returns the organizations's users.
     *
     * @return ActiveQueryInterface
     */
    public function getUserAssociations(): ActiveQueryInterface
    {
        return $this->hasMany(OrganizationUserRecord::class, ['organizationId' => 'id'])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * Returns the organizations's users.
     *
     * @return ActiveQueryInterface
     */
    public function getUsers(): ActiveQueryInterface
    {
        // Todo - apply order by
        return $this->hasMany(UserRecord::class, ['id' => 'userId'])
            ->via('userAssociations');
    }
}
