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
use flipbox\organizations\db\UserTypeAssociationQuery;
use flipbox\organizations\records\UserTypeAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserTypeAssociationQuery parentGetQuery($config = [])
 * @method UserTypeAssociation create(array $attributes = [])
 * @method UserTypeAssociation find($identifier)
 * @method UserTypeAssociation get($identifier)
 * @method UserTypeAssociation findByCondition($condition = [])
 * @method UserTypeAssociation getByCondition($condition = [])
 * @method UserTypeAssociation findByCriteria($criteria = [])
 * @method UserTypeAssociation getByCriteria($criteria = [])
 * @method UserTypeAssociation[] findAllByCondition($condition = [])
 * @method UserTypeAssociation[] getAllByCondition($condition = [])
 * @method UserTypeAssociation[] findAllByCriteria($criteria = [])
 * @method UserTypeAssociation[] getAllByCriteria($criteria = [])
 */
class UserTypeAssociations extends SortableAssociations
{
    use Accessor {
        getQuery as parentGetQuery;
    }

    /**
     * @return string
     */
    const SOURCE_ATTRIBUTE = UserTypeAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return string
     */
    const TARGET_ATTRIBUTE = UserTypeAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return UserTypeAssociation::tableAlias();
    }

    /**
     * @param array $config
     * @return SortableAssociationQueryInterface|UserTypeAssociationQuery
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
        return UserTypeAssociation::class;
    }

    /**
     * @param SortableAssociationInterface|UserTypeAssociation $record
     * @return SortableAssociationQueryInterface|UserTypeAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE}
        );
    }

    /**
     * @param SortableAssociationQueryInterface|UserTypeAssociationQuery $query
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
     * @return UserTypeAssociationQuery
     */
    private function query(
        int $userAssociationId
    ): UserTypeAssociationQuery {
        return $this->getQuery()
            ->where([
                static::SOURCE_ATTRIBUTE => $userAssociationId
            ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param int $userAssociationId
     * @return UserTypeAssociation[]
     */
    private function associations(
        int $userAssociationId
    ): array {
        return $this->query($userAssociationId)
            ->indexBy('typeId')
            ->all();
    }
}
