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
        /** @var UserQuery $query */
        /** @noinspection PhpUndefinedMethodInspection */
        $query = User::find()
            ->organization($this->organization)
            ->orderBy([
                'userOrder' => SORT_ASC,
                'username' => SORT_ASC,
            ]);

        if (null === $this->relations) {
            return Collection::make(
                $query->all()
            );
        };

        return Collection::make(
            $query
                ->id($this->relations->sortBy('userOrder')->pluck('userId'))
                ->fixedOrder(true)
                ->limit(null)
                ->all()
        );
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @param array $criteria
     * @return UserAssociationQuery
     */
    protected function query(array $criteria = []): UserAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = UserAssociation::find()
            ->setOrganizationId($this->organization->getId() ?: false)
            ->orderBy([
                'userOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * @param $object
     * @return UserAssociation
     */
    protected function create($object): UserAssociation
    {
        return (new UserAssociation())
            ->setOrganization($this->organization)
            ->setUser($this->resolveUser($object));
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function associationDelta(): array
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
        if (null === ($element = $this->resolveUser($object))) {
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
    protected function resolveUser($user = null)
    {
        if (null === $user) {
            return null;
        }

        if ($user instanceof UserAssociation) {
            return $user->getUser();
        }

        if ($user instanceof User) {
            return $user;
        }

        if (is_array($user) &&
            null !== ($id = ArrayHelper::getValue($user, 'id'))
        ) {
            $user = ['id' => $id];
        }

        return User::findOne($user);
    }
}
