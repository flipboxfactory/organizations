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
use flipbox\organizations\relationships\OrganizationTypeRelationship;
use flipbox\organizations\relationships\RelationshipInterface;
use flipbox\organizations\relationships\UserRelationship;
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
     * @var RelationshipInterface
     */
    private $userManager;

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
            $this->getUsers()->clear()->add($users);
        }

        return $this;
    }


    /************************************************************
     * USERS
     ************************************************************/

    /**
     * Get an array of users associated to an organization
     *
     * @return UserRelationship|RelationshipInterface
     */
    public function getUsers(): RelationshipInterface
    {
        if (null === $this->userManager) {
            $this->userManager = new UserRelationship($this);
        }

        return $this->userManager;
    }

    /**
     * Set an array or query of users to an organization
     *
     * @param $users
     * @return $this
     */
    public function setUsers($users)
    {
        $this->getUsers()->clear()->add($users);
        return $this;
    }
}
