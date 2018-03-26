<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\elements\traits;

use Craft;
use craft\db\Query;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organization\Organizations as OrganizationPlugin;
use flipbox\organization\records\UserAssociation as OrganizationUsersRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UsersAttribute
{
    /**
     * @var UserQuery
     */
    private $users;

    /**
     * @param array $sourceElements
     * @return array
     */
    private static function eagerLoadingUsersMap(array $sourceElements)
    {
        // Get the source element IDs
        $sourceElementIds = ArrayHelper::getColumn($sourceElements, 'id');

        $map = (new Query())
            ->select(['organizationId as source', 'userId as target'])
            ->from(OrganizationUsersRecord::tableName())
            ->where(['organizationId' => $sourceElementIds])
            ->all();

        return [
            'elementType' => User::class,
            'map' => $map
        ];
    }

    /************************************************************
     * REQUEST
     ************************************************************/

    /**
     * Associate an array of users from request input
     *
     * @param string $identifier
     * @return $this
     */
    public function setUsersFromRequest(string $identifier = 'users')
    {
        if ($users = Craft::$app->getRequest()->getBodyParam($identifier, [])) {
            // Set users array
            $this->setUsers($users);
        }

        return $this;
    }

    /************************************************************
     * USERS
     ************************************************************/

    /**
     * Get an array of users associated to an organization
     *
     * @param array $criteria
     * @return UserQuery
     */
    public function getUsers($criteria = [])
    {
        if (null === $this->users) {
            $this->users = OrganizationPlugin::getInstance()->getUsers()->getQuery([
                'organization' => $this
            ]);
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->users,
                $criteria
            );
        }

        return $this->users;
    }

    /**
     * Associate users to an organization
     *
     * @param $users
     * @return $this
     */
    public function setUsers($users)
    {
        if ($users instanceof UserQuery) {
            $this->users = $users;
            return $this;
        }

        // Reset the query
        $this->users = OrganizationPlugin::getInstance()->getUsers()->getQuery([
            'organizations' => $this
        ]);

        // Remove all users
        $this->users->setCachedResult([]);

        $this->addUsers($users);

        return $this;
    }

    /**
     * Associate an array of users to an organization
     *
     * @param $users
     * @return $this
     */
    public function addUsers(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            // Ensure we have a model
            if (!$user instanceof User) {
                $user = OrganizationPlugin::getInstance()->getUsers()->resolve($user);
            }

            $this->addUser($user);
        }

        return $this;
    }

    /**
     * Associate a user to an organization
     *
     * @param User $user
     * @return $this
     */
    public function addUser(User $user)
    {

        $currentUsers = $this->getUsers()->all();

        $userElementsByEmail = ArrayHelper::index(
            $currentUsers,
            'email'
        );

        // Does the user already exist?
        if (!array_key_exists($user->email, $userElementsByEmail)) {
            $currentUsers[] = $user;
            $this->getUsers()->setCachedResult($currentUsers);
        }

        return $this;
    }

    /**
     * Dissociate a user from an organization
     *
     * @param array $users
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function removeUsers(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            // Ensure we have a model
            if (!$user instanceof User) {
                $user = OrganizationPlugin::getInstance()->getUsers()->resolve($user);
            }

            $this->removeUser($user);
        }

        return $this;
    }

    /**
     * Dissociate a user from an organization
     *
     * @param User $user
     * @return $this
     */
    public function removeUser(User $user)
    {
        $userElementsByEmail = ArrayHelper::index(
            $this->getUsers()->all(),
            'email'
        );

        // Does the user already exist?
        if (array_key_exists($user->email, $userElementsByEmail)) {
            unset($userElementsByEmail[$user->email]);

            $this->getUsers()->setCachedResult(
                array_values($userElementsByEmail)
            );
        }

        return $this;
    }

    /**
     * Reset users
     *
     * @return $this
     */
    public function resetUsers()
    {
        $this->users = null;
        return $this;
    }
}
