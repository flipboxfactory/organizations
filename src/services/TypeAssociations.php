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
use flipbox\organizations\db\TypeAssociationQuery;
use flipbox\organizations\records\TypeAssociation;
use flipbox\organizations\records\TypeAssociation as TypeAssociationRecord;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method TypeAssociationQuery parentGetQuery($config = [])
 * @method TypeAssociation create(array $attributes = [])
 * @method TypeAssociation find($identifier)
 * @method TypeAssociation get($identifier)
 * @method TypeAssociation findByCondition($condition = [])
 * @method TypeAssociation getByCondition($condition = [])
 * @method TypeAssociation findByCriteria($criteria = [])
 * @method TypeAssociation getByCriteria($criteria = [])
 * @method TypeAssociation[] findAllByCondition($condition = [])
 * @method TypeAssociation[] getAllByCondition($condition = [])
 * @method TypeAssociation[] findAllByCriteria($criteria = [])
 * @method TypeAssociation[] getAllByCriteria($criteria = [])
 */
class TypeAssociations extends SortableAssociations
{
    use Accessor {
        getQuery as parentGetQuery;
    }

    /**
     * @return string
     */
    const SOURCE_ATTRIBUTE = TypeAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return string
     */
    const TARGET_ATTRIBUTE = TypeAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return TypeAssociation::tableAlias();
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
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return TypeAssociation::class;
    }

    /**
     * @param SortableAssociationInterface|TypeAssociationRecord $record
     * @return SortableAssociationQueryInterface|TypeAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE}
        );
    }

    /**
     * @param $source
     * @return SortableAssociationQueryInterface|TypeAssociationQuery
     */
    private function query(
        $source
    ): SortableAssociationQueryInterface {
        /** @var TypeAssociationQuery $query */
        $query = $this->getQuery();
        return $query->where([
            static::SOURCE_ATTRIBUTE => $source
        ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param SortableAssociationQueryInterface|TypeAssociationQuery $query
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
     * @return array
     */
    private function associations(
        $source
    ): array {
        /** @var TypeAssociationQuery $query */
        $query = $this->getQuery($source);
        return $query->indexBy(static::TARGET_ATTRIBUTE)
            ->all();
    }
}
