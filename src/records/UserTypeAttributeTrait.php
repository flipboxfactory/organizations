<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\records;

use flipbox\craft\ember\records\ActiveRecordTrait;
use flipbox\organizations\models\UserTypeRulesTrait;
use flipbox\organizations\objects\UserTypeMutatorTrait;
use flipbox\organizations\records\UserType as TypeRecord;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method TypeRecord parentResolveType()
 */
trait UserTypeAttributeTrait
{
    use ActiveRecordTrait,
        UserTypeRulesTrait,
        UserTypeMutatorTrait {
        resolveType as parentResolveType;
    }

    /**
     * Get associated typeId
     *
     * @return int|null
     */
    public function getTypeId()
    {
        $id = $this->getAttribute('typeId');
        if (null === $id && null !== $this->type) {
            $id = $this->type->id;
            $this->setAttribute('typeId', $id);
        }

        return $id !== false ? $id : null;
    }

    /**
     * @return TypeRecord|null
     */
    protected function resolveType()
    {
        if ($type = $this->resolveTypeFromRelation()) {
            return $type;
        }

        return $this->parentResolveType();
    }

    /**
     * @return TypeRecord|null
     */
    private function resolveTypeFromRelation()
    {
        if (false === $this->isRelationPopulated('typeRecord')) {
            return null;
        }

        if (null === ($record = $this->getRelation('typeRecord'))) {
            return null;
        }

        return $record instanceof TypeRecord ? $record : null;
    }

    /**
     * Get the associated Type
     *
     * @return ActiveQueryInterface
     */
    protected function getTypeRecord()
    {
        return $this->hasOne(
            TypeRecord::class,
            ['typeId' => 'id']
        );
    }
}
