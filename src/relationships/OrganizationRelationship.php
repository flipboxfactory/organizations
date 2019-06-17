<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use Tightenco\Collect\Support\Collection;

/**
 * Manages Organizations associated to Users
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 * *
 * @method UserAssociation findOrCreate($object)
 * @method UserAssociation findOne($object = null)
 * @method UserAssociation findOrFail($object)
 */
class OrganizationRelationship implements RelationshipInterface
{
    use RelationshipTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /************************************************************
     * COLLECTION
     ************************************************************/

    /**
     * @inheritDoc
     * @return Organization[]|Collection
     */
    public function getCollection(): Collection
    {
        return $this->getRelationships()
            ->sortBy('organizationOrder')
            ->pluck('organization');
    }

    /**
     * @inheritDoc
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        $relationships = $this->query()
            ->with('types')
            ->all();

        // 'eager' load where we'll pre-populate all of the associations
        $elements = Organization::find()
            ->id(array_keys($relationships))
            ->anyStatus()
            ->limit(null)
            ->indexBy('id')
            ->all();

        return (new Collection($relationships))
            ->transform(function (UserAssociation $association, $key) use ($elements) {
                if (isset($elements[$key])) {
                    $association->setOrganization($elements[$key]);
                    $association->setUser($this->user);
                }
                return $association;
            });
    }

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return UserAssociationQuery
     */
    protected function query(): UserAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return UserAssociation::find()
            ->setUserId($this->user->getId() ?: false)
            ->orderBy([
                'organizationOrder' => SORT_ASC
            ])
            ->limit(null);
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @param $object
     * @return UserAssociation
     */
    protected function create($object): UserAssociation
    {
        return (new UserAssociation())
            ->setOrganization($this->resolve($object))
            ->setUser($this->user);
    }


    /*******************************************
     * DELTA
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('organizationId')
            ->all();

        $associations = [];
        $order = 1;

        /** @var UserAssociation $newAssociation */
        foreach ($this->getRelationships()->sortBy('organizationOrder') as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                    $existingAssociations,
                    $newAssociation->getOrganizationId()
                ))) {
                $association = $newAssociation;
            } elseif ($newAssociation->getTypes()->isMutated()) {
                /** @var UserAssociation $association */
                $association->getTypes()->clear()->add(
                    $newAssociation->getTypes()->getCollection()
                );
            }

            $association->userOrder = $newAssociation->userOrder;
            $association->organizationOrder = $order++;
            $association->state = $newAssociation->state;

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }


    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @param UserAssociation|Organization|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if (null === ($element = $this->resolve($object))) {
            Organizations::info(sprintf(
                "Unable to resolve organization: %s",
                (string)Json::encode($object)
            ));
            return null;
        }

        /** @var UserAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->getOrganizationId() == $element->getId()) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserAssociation|Organization|int|array $organization
     * @return Organization|null
     */
    protected function resolveObject($organization)
    {
        if ($organization instanceof UserAssociation) {
            return $organization->getOrganization();
        }

        if ($organization instanceof Organization) {
            return $organization;
        }

        return Organization::findOne($organization);
    }
}
