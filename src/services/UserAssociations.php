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
use flipbox\organizations\db\UserAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserAssociationQuery parentGetQuery($config = [])
 * @method UserAssociation create(array $attributes = [])
 * @method UserAssociation find($identifier)
 * @method UserAssociation get($identifier)
 * @method UserAssociation findByCondition($condition = [])
 * @method UserAssociation getByCondition($condition = [])
 * @method UserAssociation findByCriteria($criteria = [])
 * @method UserAssociation getByCriteria($criteria = [])
 * @method UserAssociation[] findAllByCondition($condition = [])
 * @method UserAssociation[] getAllByCondition($condition = [])
 * @method UserAssociation[] findAllByCriteria($criteria = [])
 * @method UserAssociation[] getAllByCriteria($criteria = [])
 */
class UserAssociations extends SortableAssociations
{
    use Accessor {
        getQuery as parentGetQuery;
    }

    /**
     * @return string
     */
    const SOURCE_ATTRIBUTE = UserAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return string
     */
    const TARGET_ATTRIBUTE = UserAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return UserAssociation::tableAlias();
    }

    /**
     * @param array $config
     * @return SortableAssociationQueryInterface|ActiveQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        return $this->parentGetQuery($config);
    }

    /**
     * @return string
     */
    public static function recordClass()
    {
        return UserAssociation::class;
    }

    /**
     * @param SortableAssociationInterface|UserAssociation $record
     * @return SortableAssociationQueryInterface|UserAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE}
        );
    }

    /**
     * @param SortableAssociationQueryInterface|UserAssociationQuery $query
     * @return array
     */
    protected function existingAssociations(
        SortableAssociationQueryInterface $query
    ): array {
        $source = $this->resolveStringAttribute($query, static::SOURCE_ATTRIBUTE);

        if ($source === null) {
            return [];
        }

        return $this->associations($source);
    }

    /**
     * @param $source
     * @return SortableAssociationQueryInterface|UserAssociationQuery
     */
    private function query(
        $source
    ): SortableAssociationQueryInterface {
        /** @var UserAssociationQuery $query */
        $query = $this->getQuery();
        return $query->where([
            static::SOURCE_ATTRIBUTE => $source
        ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param $source
     * @return array
     */
    private function associations(
        $source
    ): array {
        /** @var UserAssociationQuery $query */
        $query = $this->getQuery($source);
        return $query->indexBy(static::TARGET_ATTRIBUTE)
            ->all();
    }
}
