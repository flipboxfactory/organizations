<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\objects;

/**
 * @property int|null $organizationId
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationAttributeTrait
{
    use OrganizationMutatorTrait;

    /**
     * @var int|null
     */
    private $organizationId;

    /**
     * @inheritDoc
     */
    protected function internalSetOrganizationId(int $id = null)
    {
        $this->organizationId = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function internalGetOrganizationId()
    {
        return $this->organizationId === null ? null : (int) $this->organizationId;
    }
}
