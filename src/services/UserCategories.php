<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\db\Query;
use craft\elements\User as UserElement;
use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\services\traits\records\AccessorByString;
use flipbox\organizations\db\UserCategoryQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserCategory;
use flipbox\organizations\records\UserCategoryAssociation;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserCategory create(array $attributes = [], string $toScenario = null)
 * @method UserCategory find($identifier, string $toScenario = null)
 * @method UserCategory get($identifier, string $toScenario = null)
 * @method UserCategory findByString($identifier, string $toScenario = null)
 * @method UserCategory getByString($identifier, string $toScenario = null)
 * @method UserCategory findByCondition($condition = [], string $toScenario = null)
 * @method UserCategory getByCondition($condition = [], string $toScenario = null)
 * @method UserCategory findByCriteria($criteria = [], string $toScenario = null)
 * @method UserCategory getByCriteria($criteria = [], string $toScenario = null)
 * @method UserCategory[] findAll(string $toScenario = null)
 * @method UserCategory[] findAllByCondition($condition = [], string $toScenario = null)
 * @method UserCategory[] getAllByCondition($condition = [], string $toScenario = null)
 * @method UserCategory[] findAllByCriteria($criteria = [], string $toScenario = null)
 * @method UserCategory[] getAllByCriteria($criteria = [], string $toScenario = null)
 * @method UserCategoryQuery getQuery($config = []): ActiveQuery($criteria = [], string $toScenario = null)
 */
class UserCategories extends Component
{
    use AccessorByString;

    /**
     * @return string
     */
    protected static function stringProperty(): string
    {
        return 'handle';
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return UserCategory::class;
    }

    /**
     * @param mixed $category
     * @return UserCategory
     */
    public function resolve($category)
    {
        if ($category = $this->find($category)) {
            return $category;
        }

        $category = ArrayHelper::toArray($category, [], false);

        try {
            $object = $this->create($category);
        } catch (\Exception $e) {
            $object = new UserCategory();
            ObjectHelper::populate(
                $object,
                $category
            );
        }

        return $object;
    }

    /**
     * @param UserCategoryQuery $query
     * @param UserElement $user
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        UserCategoryQuery $query,
        UserElement $user,
        OrganizationElement $organization
    ): bool {
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = Organizations::getInstance()->getUserCategoryAssociations();

        $userAssociationId = $this->associationId($user->getId(), $organization->getId());

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $userAssociationId
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $userAssociationId)
        );

        return $associationService->save($query);
    }


    /*******************************************
     * USER / ORGANIZATION ASSOCIATION ID
     *******************************************/

    /**
     * @param array $categories
     * @param int $userAssociationId
     * @return array
     */
    private function toAssociations(
        array $categories,
        int $userAssociationId
    ) {
        $associations = [];
        foreach ($categories as $category) {
            $associations[] = new UserCategoryAssociation([
                'categoryId' => $category->id,
                'userId' => $userAssociationId
            ]);
        }

        return $associations;
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return Query
     */
    private function associationIdQuery(int $userId, int $organizationId): Query
    {
        return (new Query())
            ->select(['id'])
            ->from([UserAssociation::tableName()])
            ->where([
                'organizationId' => $organizationId,
                'userId' => $userId,
            ]);
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return string|null
     */
    private function associationId(int $userId, int $organizationId)
    {
        $id = $this->associationIdQuery($userId, $organizationId)->scalar();
        return is_string($id) ? $id : null;
    }
}
