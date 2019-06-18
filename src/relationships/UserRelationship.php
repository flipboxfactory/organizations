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
use flipbox\organizations\elements\Organization;
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
        if (null === $this->relations) {
            return new Collection(
                $this->elementQuery()
                    ->all()
            );
        }

        return $this->getRelationships()
            ->sortBy('userOrder')
            ->pluck('user');
    }

    /**
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        $relationships = $this->associationQuery()
            ->with('types')
            ->all();

        // 'eager' load where we'll pre-populate all of the associations
        $elements = $this->elementQuery()
            ->id(array_keys($relationships))
            ->indexBy('id')
            ->all();

        return $this->createRelations($relationships)
            ->transform(function (UserAssociation $association) use ($elements) {
                if (isset($elements[$association->getUserId()])) {
                    $association->setUser($elements[$association->getUserId()]);
                    $association->setOrganization($this->organization);
                }
                return $association;
            });
    }

    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return UserQuery
     */
    protected function elementQuery(): UserQuery
    {
        return User::find()
            ->organizationId($this->organization->getId() ?: false)
            ->anyStatus()
            ->limit(null);
    }

    /**
     * @return UserAssociationQuery
     */
    protected function associationQuery(): UserAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return UserAssociation::find()
            ->setOrganizationId($this->organization->getId() ?: false)
            ->orderBy([
                'organizationOrder' => SORT_ASC
            ])
            ->limit(null);
    }

    /************************************************************
     * CREATE
     ************************************************************/

    /**
     * @param $object
     * @return UserAssociation
     */
    protected function create($object): UserAssociation
    {
        if ($object instanceof UserAssociation) {
            return $object;
        }

        return (new UserAssociation())
            ->setOrganization($this->organization)
            ->setUser($this->resolveObject($object));
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->associationQuery()
            ->indexBy('userId')
            ->all();

        $associations = [];
        $order = 1;

        /** @var UserAssociation $newAssociation */
        foreach ($this->getRelationships() as $newAssociation) {
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

            $association->ignoreSortOrder();

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }


    /*******************************************
     * COLLECTION
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, UserAssociation $association)
    {
        if ($association->userOrder > 0) {
            $collection->splice($association->userOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, UserAssociation $association)
    {
        if ($key = $this->findKey($association)) {
            $collection->offsetUnset($key);
        }

        $this->insertCollection($collection, $association);
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
        if ($object instanceof UserAssociation) {
            if (!$object->getUser()) {
                return null;
            }

            return $this->findRelationshipKey($object->getUser()->email);
        }

        if (null === ($element = $this->resolveObject($object))) {
            return null;
        }

        return $this->findRelationshipKey($element->email);
    }

    /**
     * @param $identifier
     * @return int|string|null
     */
    protected function findRelationshipKey($identifier)
    {
        /** @var UserAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if (null !== $association->getUser() && $association->getUser()->email == $identifier) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param UserAssociation|User|int|array|null $user
     * @return User|null
     */
    protected function resolveObjectInternal($user)
    {
        if ($user instanceof UserAssociation) {
            return $user->getUser();
        }

        if ($user instanceof User) {
            return $user;
        }

        if (is_numeric($user)) {
            return \Craft::$app->getUsers()->getUserById($user);
        }

        if (is_string($user)) {
            return \Craft::$app->getUsers()->getUserByUsernameOrEmail($user);
        }

        return User::findOne($user);
    }
}
