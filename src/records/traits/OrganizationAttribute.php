<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\records\traits;

use flipbox\ember\records\traits\ActiveRecord;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\Organization;
use flipbox\organizations\traits\OrganizationMutator;
use flipbox\organizations\traits\OrganizationRules;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Organization[] $organizationRecord
 */
trait OrganizationAttribute
{
    use ActiveRecord,
        OrganizationRules,
        OrganizationMutator;

    /**
     * Get associated organizationId
     *
     * @return int|null
     */
    public function getOrganizationId()
    {
        $id = $this->getAttribute('organizationId');
        if (null === $id && null !== $this->organization) {
            $id = $this->organization->id;
            $this->setAttribute('organizationId', $id);
        }

        return $id;
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

        /** @var Organization $record */
        $record = $this->getRelation('organizationRecord');
        if (null === $record) {
            return null;
        }

        return OrganizationPlugin::getInstance()->getOrganizations()->find($record->id);
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
