<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\objects;

use Craft;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\organizations\elements\Organization;

/**
 * @property int|null $organizationId
 * @property Organization|null $organization
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationMutatorTrait
{
    /**
     * @var Organization|null
     */
    private $organization;

    /**
     * @return bool
     */
    public function isOrganizationSet(): bool
    {
        return null !== $this->organization;
    }

    /**
     * Set associated organizationId
     *
     * @param $id
     * @return $this
     */
    public function setOrganizationId(int $id)
    {
        $this->organizationId = $id;
        return $this;
    }

    /**
     * Get associated organizationId
     *
     * @return int|null
     */
    public function getOrganizationId()
    {
        if (null === $this->organizationId && null !== $this->organization) {
            $this->organizationId = $this->organization->id;
        }

        return $this->organizationId;
    }

    /**
     * AssociateUserToOrganization a organization
     *
     * @param mixed $organization
     * @return $this
     */
    public function setOrganization($organization = null)
    {
        $this->organization = null;

        if (null === ($organization = $this->internalResolveOrganization($organization))) {
            $this->organization = $this->organizationId = null;
        } else {
            $this->organizationId = $organization->id;
            $this->organization = $organization;
        }

        return $this;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization()
    {
        if ($this->organization === null) {
            $organization = $this->resolveOrganization();
            $this->setOrganization($organization);
            return $organization;
        }

        $organizationId = $this->organizationId;
        if ($organizationId !== null &&
            $organizationId !== $this->organization->id
        ) {
            $this->organization = null;
            return $this->getOrganization();
        }

        return $this->organization;
    }

    /**
     * @return Organization|null
     */
    protected function resolveOrganization()
    {
        if ($model = $this->resolveOrganizationFromId()) {
            return $model;
        }

        return null;
    }

    /**
     * @return Organization|null
     */
    private function resolveOrganizationFromId()
    {
        if (null === $this->organizationId) {
            return null;
        }

        return Organization::findOne($this->organizationId);
    }

    /**
     * @param $organization
     * @return Organization|null
     */
    protected function internalResolveOrganization($organization = null)
    {
        if (null === $organization) {
            return null;
        }

        if ($organization instanceof Organization) {
            return $organization;
        }

        if (is_numeric($organization) || is_string($organization)) {
            return Organization::findOne($organization);
        }

        try {
            $object = Craft::createObject(Organization::class, [$organization]);
        } catch (\Exception $e) {
            $object = new Organization();
            ObjectHelper::populate(
                $object,
                $organization
            );
        }

        /** @var Organization $object */
        return $object;
    }
}
