<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\elements\db\UserQuery;
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
 * Manages Users associated to Organizations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @method UserAssociation findOrCreate($object)
 * @method UserAssociation findOne($object = null)
 * @method UserAssociation findOrFail($object)
 */
class UserRelationship implements RelationshipInterface
{
    use RelationshipTrait;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }


    /************************************************************
     * COLLECTION
     ************************************************************/

    /**
     * Get a collection of associated users
     *
     * @return User[]|Collection
     */
    public function getCollection(): Collection
    {
        return $this->getRelationships()
            ->sortBy('userOrder')
            ->pluck('user');
    }

    /**
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        $relationships = $this->query()
            ->with('types')
            ->all();

        // 'eager' load where we'll pre-populate all of the associations
        $elements = User::find()
            ->id(array_keys($relationships))
            ->anyStatus()
            ->limit(null)
            ->indexBy('id')
            ->all();

        return (new Collection($relationships))
            ->transform(function (UserAssociation $association, $key) use ($elements) {
                if (isset($elements[$key])) {
                    $association->setUser($elements[$key]);
                    $association->setOrganization($this->organization);
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
            ->setOrganizationId($this->organization->getId() ?: false)
            ->orderBy([
                'userOrder' => SORT_ASC
            ])
            ->limit(null)
            ->indexBy('userId');
    }

    /**
     * @param $object
     * @return UserAssociation
     */
    protected function create($object): UserAssociation
    {
        return (new UserAssociation())
            ->setOrganization($this->organization)
            ->setUser($this->resolve($object));
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('userId')
            ->all();

        $associations = [];
        $order = 1;
        /** @var UserAssociation $newAssociation */
        foreach ($this->getRelationships()->sortBy('userOrder') as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                    $existingAssociations,
                    $newAssociation->getUserId()
                ))) {
                $association = $newAssociation;
            } elseif ($newAssociation->getTypes()->isMutated()) {
                /** @var UserAssociation $association */
                $association->getTypes()->clear()->add(
                    $newAssociation->getTypes()->getCollection()
                );
            }

            $association->userOrder = $order++;
            $association->organizationOrder = $newAssociation->organizationOrder;
            $association->state = $newAssociation->state;

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @inheritDoc
     */
    protected function handleAssociationError()
    {
        $this->organization->addError('users', 'Unable to save user organizations.');
    }


    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @param UserAssociation|User|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if (null === ($element = $this->resolve($object))) {
            Organizations::info(sprintf(
                "Unable to resolve user: %s",
                (string)Json::encode($object)
            ));
            return null;
        }

        /** @var UserAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if (null !== $association->getUser() && $association->getUser()->email == $element->email) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserAssociation|User|int|array|null $user
     * @return User|null
     */
    protected function resolveObject($user)
    {
        if ($user instanceof UserAssociation) {
            return $user->getUser();
        }

        if ($user instanceof User) {
            return $user;
        }

        return User::findOne($user);
    }
}
