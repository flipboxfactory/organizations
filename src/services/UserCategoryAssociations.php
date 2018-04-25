<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\organizations\db\UserCategoryAssociationQuery;
use flipbox\organizations\records\UserCategoryAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserCategoryAssociationQuery parentGetQuery($config = [])
 * @method UserCategoryAssociation create(array $attributes = [])
 * @method UserCategoryAssociation find($identifier)
 * @method UserCategoryAssociation get($identifier)
 * @method UserCategoryAssociation findByCondition($condition = [])
 * @method UserCategoryAssociation getByCondition($condition = [])
 * @method UserCategoryAssociation findByCriteria($criteria = [])
 * @method UserCategoryAssociation getByCriteria($criteria = [])
 * @method UserCategoryAssociation[] findAllByCondition($condition = [])
 * @method UserCategoryAssociation[] getAllByCondition($condition = [])
 * @method UserCategoryAssociation[] findAllByCriteria($criteria = [])
 * @method UserCategoryAssociation[] getAllByCriteria($criteria = [])
 */
class UserCategoryAssociations extends SortableAssociations
{
    use Accessor {
        getQuery as parentGetQuery;
    }

    /**
     * @return string
     */
    const SOURCE_ATTRIBUTE = UserCategoryAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return string
     */
    const TARGET_ATTRIBUTE = UserCategoryAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return UserCategoryAssociation::tableAlias();
    }

    /**
     * @param array $config
     * @return SortableAssociationQueryInterface|UserCategoryAssociationQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        return $this->parentGetQuery($config);
    }

    /**
     * @return string
     */
    public static function recordClass(): string
    {
        return UserCategoryAssociation::class;
    }

    /**
     * @param SortableAssociationInterface|UserCategoryAssociation $record
     * @return SortableAssociationQueryInterface|UserCategoryAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE}
        );
    }

    /**
     * @param SortableAssociationQueryInterface|UserCategoryAssociationQuery $query
     * @return array
     */
    protected function existingAssociations(
        SortableAssociationQueryInterface $query
    ): array {
        if (null === ($associationId = $this->resolveStringAttribute($query, static::SOURCE_ATTRIBUTE))) {
            return [];
        }

        return $this->associations((int)$associationId);
    }

    /**
     * @param int $userAssociationId
     * @return UserCategoryAssociationQuery
     */
    private function query(
        int $userAssociationId
    ): UserCategoryAssociationQuery {
        return $this->getQuery()
            ->where([
                static::SOURCE_ATTRIBUTE => $userAssociationId
            ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param int $userAssociationId
     * @return UserCategoryAssociation[]
     */
    private function associations(
        int $userAssociationId
    ): array {
        return $this->query($userAssociationId)
            ->indexBy('categoryId')
            ->all();
    }
}
