<?php

namespace flipbox\organizations\behaviors;

use craft\elements\User;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\relationships\RelationshipInterface;
use flipbox\organizations\relationships\UserTypeRelationship;
use flipbox\organizations\queries\UserTypeQuery;
use flipbox\organizations\records\UserType;
use Tightenco\Collect\Support\Collection;
use yii\base\Behavior;
use yii\base\Exception;

/**
 * @property User|OrganizationsAssociatedToUserBehavior $owner;
 */
class UserTypesAssociatedToUserBehavior extends Behavior
{
    /**
     * @param Organization|int $organization
     * @return RelationshipInterface
     * @throws Exception
     */
    public function getUserTypes($organization): RelationshipInterface
    {
        return $this->owner->getOrganizations()
            ->findOrFail($organization)
            ->getTypes();
    }

    /**
     * @param array $criteria
     * @return UserTypeQuery
     */
    public function userTypeQuery($criteria = []): UserTypeQuery
    {
        $query = UserType::find()
            ->user($this->owner);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }
}
