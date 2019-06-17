<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\records;

use flipbox\craft\ember\records\ActiveRecordTrait;
use flipbox\organizations\objects\OrganizationTypeMutatorTrait;
use flipbox\organizations\records\UserType as TypeRecord;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecordInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property OrganizationType|null $typeRecord
 */
trait OrganizationTypeAttributeTrait
{
    use ActiveRecordTrait,
        OrganizationTypeMutatorTrait;

    /**
     * Populates the named relation with the related records.
     * Note that this method does not check if the relation exists or not.
     * @param string $name the relation name, e.g. `orders` for a relation defined via `getOrders()` method (case-sensitive).
     * @param ActiveRecordInterface|array|null $records the related records to be populated into the relation.
     * @see getRelation()
     */
    abstract public function populateRelation($name, $records);

    /**
     * @return OrganizationType|null
     */
    protected function internalGetType()
    {
        return $this->typeRecord;
    }

    /**
     * @param int|null $id
     */
    protected function internalSetTypeId(int $id = null)
    {
        $this->setAttribute('typeId', $id);
    }

    /**
     * @return int|null
     */
    protected function internalGetTypeId()
    {
        return $this->getAttribute('typeId');
    }

    /**
     * @param OrganizationType|null $type
     */
    protected function internalSetType(OrganizationType $type = null)
    {
        $this->populateRelation('typeRecord', $type);
    }
    
    /**
     * @return array
     */
    protected function typeRules(): array
    {
        return [
            [
                [
                    'typeId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'typeId',
                    'type'
                ],
                'safe',
                'on' => [
                    Model::SCENARIO_DEFAULT
                ]
            ]
        ];
    }

    /**
     * @return ActiveQueryInterface
     */
    protected function getTypeRecord()
    {
        return $this->hasOne(
            OrganizationType::class,
            ['id' => 'typeId']
        );
    }
}
