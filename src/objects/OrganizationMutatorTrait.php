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
 * This trait accepts both an Organization or and Organization Id and ensures that the both
 * the Organization and the Id are in sync; If one changes (and does not match the other) it
 * resolves (removes / updates) the other.
 *
 * In addition, this trait is primarily useful when a new Organization is set and saved; the Organization
 * Id can be retrieved without needing to explicitly set the newly created Id.
 *
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
     * Internally set the Organization Id.  This can be overridden. A record for example
     * should use `setAttribute`.
     *
     * @param int|null $id
     * @return $this
     */
    abstract protected function internalSetOrganizationId(int $id = null);

    /**
     * Internally get the Organization Id.  This can be overridden.  A record for example
     * should use `getAttribute`.
     *
     * @return int|null
     */
    abstract protected function internalGetOrganizationId();

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
    public function setOrganizationId(int $id = null)
    {
        $this->internalSetOrganizationId($id);

        if (null !== $this->organization && $id != $this->organization->id) {
            $this->organization = null;
        }

        return $this;
    }

    /**
     * Get associated organizationId
     *
     * @return int|null
     */
    public function getOrganizationId()
    {
        if (null === $this->internalGetOrganizationId() && null !== $this->organization) {
            $this->setOrganizationId($this->organization->id);
        }

        return $this->internalGetOrganizationId();
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
        $this->internalSetOrganizationId(null);

        if (null !== ($organization = $this->verifyOrganization($organization))) {
            $this->organization = $organization;
            $this->internalSetOrganizationId($organization->id);
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

        $organizationId = $this->internalGetOrganizationId();
        if ($organizationId !== null && $organizationId != $this->organization->id) {
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
        if ($organization = $this->resolveOrganizationFromId()) {
            return $organization;
        }

        return null;
    }

    /**
     * @return Organization|null
     */
    private function resolveOrganizationFromId()
    {
        if (null === ($organizationId = $this->internalGetOrganizationId())) {
            return null;
        }

        return Organization::findOne($organizationId);
    }

    /**
     * Attempt to verify that the passed 'organization' is a valid element.  A primary key or query
     * can be passed to lookup an organization.
     *
     * @param mixed $organization
     * @return Organization|null
     */
    protected function verifyOrganization($organization = null)
    {
        if (null === $organization) {
            return null;
        }

        if ($organization instanceof Organization) {
            return $organization;
        }

        return Organization::findOne($organization);
    }
}
