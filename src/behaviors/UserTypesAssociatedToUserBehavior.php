<?php

namespace flipbox\organizations\behaviors;

use craft\elements\User;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\managers\UserTypeAssociationManager;
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
     * @return UserTypeAssociationManager
     * @throws Exception
     */
    public function getUserTypeManager($organization): UserTypeAssociationManager
    {
        return $this->owner->getOrganizationManager()
            ->findOrFail($organization)
            ->getTypeManager();
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

    /**
     * Get a query with associated user types
     *
     * @return UserType[]|Collection
     */
    public function getUserTypes(): Collection
    {
        return Collection::make($this->userTypeQuery()->all());
    }
}
