<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements;

use Craft;
use craft\db\Query;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\managers\RelationshipManagerInterface;
use flipbox\organizations\managers\UserRelationshipManager;
use flipbox\organizations\records\UserAssociation;
use Tightenco\Collect\Support\Collection;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @mixin Organization
 */
trait UsersAttributeTrait
{
    /**
     * @var RelationshipManagerInterface
     */
    private $userManager;

    /**
     * @return RelationshipManagerInterface
     */
    public function getUserManager(): RelationshipManagerInterface
    {
        if (null === $this->userManager) {
            $this->userManager = new UserRelationshipManager($this);
        }

        return $this->userManager;
    }

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
            ->from(UserAssociation::tableName())
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
     * AssociateUserToOrganization an array of users from request input
     *
     * @param string $identifier
     * @return $this
     */
    public function setUsersFromRequest(string $identifier = 'users')
    {
        if (null !== ($users = Craft::$app->getRequest()->getBodyParam($identifier))) {
            $this->getUserManager()->setMany((array)$users);
        }

        return $this;
    }


    /************************************************************
     * USERS QUERY
     ************************************************************/

    /**
     * @param array $criteria
     * @return UserQuery
     */
    public function userQuery($criteria = []): UserQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = User::find()
            ->organization($this)
            ->orderBy([
                'userOrder' => SORT_ASC,
                'username' => SORT_ASC,
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
     * Get an array of users associated to an organization
     *
     * @param array $criteria
     * @return User[]|Collection
     */
    public function getUsers(array $criteria = []): Collection
    {
        return Collection::make(QueryHelper::configure(
            $this->userQuery($criteria)
                ->id(
                    $this->getUserManager()->findAll()
                        ->pluck('userId')
                )
                ->fixedOrder(true)
                ->limit(null),
            $criteria
        )->all());
    }

    /**
     * Set an array or query of users to an organization
     *
     * @param $users
     * @return $this
     */
    public function setUsers($users)
    {
        $this->getUserManager()->setMany($users);
        return $this;
    }
}
