<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\records;

use flipbox\craft\ember\records\ActiveRecordTrait;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\models\OrganizationRulesTrait;
use flipbox\organizations\objects\OrganizationMutatorTrait;
use flipbox\organizations\Organizations;
use yii\db\ActiveQueryInterface;

/**
 * Intended to be used on an ActiveRecord, this class provides `$this->organizationId` attribute along with 'getters'
 * and 'setters' to ensure continuity between the Id and Object.  An organization object is lazy loaded when called.
 * In addition, ActiveRecord rules are available.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationAttributeTrait
{
    use ActiveRecordTrait,
        OrganizationRulesTrait,
        OrganizationMutatorTrait;

    /**
     * @inheritdoc
     */
    public function organizationAttributes(): array
    {
        return [
            'organizationId'
        ];
    }

    /**
     * @inheritdoc
     */
    public function organizationAttributeLabels(): array
    {
        return [
            'organizationId' => Organizations::t('Organization Id')
        ];
    }

    /**
     * @inheritDoc
     */
    protected function internalSetOrganizationId(int $id = null)
    {
        $this->setAttribute('organizationId', $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function internalGetOrganizationId()
    {
        if (null === ($id = $this->getAttribute('organizationId'))) {
            return null;
        }
        return (int) $id;
    }

    /**
     * @return OrganizationElement|null
     */
    protected function resolveOrganization()
    {
        if ($model = $this->resolveOrganizationFromRelation()) {
            return $model;
        }

        return $this->resolveOrganizationFromId();
    }

    /**
     * @return OrganizationElement|null
     */
    private function resolveOrganizationFromRelation()
    {
        if (false === $this->isRelationPopulated('organizationRecord')) {
            return null;
        }

        if (null === ($record = $this->getRelation('organizationRecord'))) {
            return null;
        }

        /** @var Organization $record */

        return OrganizationElement::findOne($record->id);
    }

    /**
     * Returns the associated organization record.
     *
     * @return ActiveQueryInterface
     */
    protected function getOrganizationRecord(): ActiveQueryInterface
    {
        return $this->hasOne(
            Organization::class,
            ['id' => 'organizationId']
        );
    }
}
