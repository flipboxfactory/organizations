<?php

namespace flipbox\organizations\elements\behaviors;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organizations\db\UserCategoryQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserCategory;
use yii\base\Behavior;

/**
 * @property User $owner;
 */
class UserCategoriesBehavior extends Behavior
{
    /**
     * @var UserCategoryQuery|null
     */
    private $organizationCategories;

    /**
     * @return UserCategoryQuery
     */
    private function createQuery(): UserCategoryQuery
    {
        return OrganizationPlugin::getInstance()->getUserCategories()->getQuery([
            'user' => $this->owner
        ]);
    }

    /**
     * Get a query with associated categories
     *
     * @param array $criteria
     * @return UserCategoryQuery
     */
    public function getOrganizationCategories($criteria = []): UserCategoryQuery
    {
        if (null === $this->organizationCategories) {
            $this->organizationCategories = $this->createQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->organizationCategories,
                $criteria
            );
        }

        return $this->organizationCategories;
    }

    /**
     * Associate users to an category
     *
     * @param $organizationCategories
     * @return $this
     */
    public function setOrganizationCategories($organizationCategories)
    {
        if ($organizationCategories instanceof UserCategoryQuery) {
            $this->organizationCategories = $organizationCategories;
            return $this;
        }

        // Reset the query
        $this->organizationCategories = $this->createQuery();
        $this->organizationCategories->setCachedResult([]);
        $this->addUserCategories($organizationCategories);
        return $this;
    }

    /**
     * Associate an array of users to an category
     *
     * @param $categories
     * @return $this
     */
    protected function addUserCategories(array $categories)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($categories)) {
            $categories = [$categories];
        }

        foreach ($categories as $key => $category) {
            if (!$category = OrganizationPlugin::getInstance()->getUserCategories()->resolve($category)) {
                OrganizationPlugin::warning(sprintf(
                    "Unable to resolve user category: %s",
                    (string)Json::encode($category)
                ));
                continue;
            }

            $this->addUserCategory($category);
        }

        return $this;
    }

    /**
     * Associate a user to an category
     *
     * @param UserCategory $category
     * @return $this
     */
    public function addUserCategory(UserCategory $category)
    {
        // Current associated categories
        $allCategories = $this->getOrganizationCategories()->all();
        $allCategories[] = $category;

        $this->getOrganizationCategories()->setCachedResult($allCategories);

        return $this;
    }
}
