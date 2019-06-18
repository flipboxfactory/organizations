<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\records;

use flipbox\craft\ember\records\ActiveRecordTrait;
use flipbox\organizations\objects\OrganizationTypeMutatorTrait;
use yii\base\Model;
use yii\db\ActiveQueryInterface;

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
