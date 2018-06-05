<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\organizations\db\OrganizationUserAssociationQuery;
use flipbox\organizations\db\UserOrganizationAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\Organizations as OrganizationPlugin;

/**
 * Manage the User associations for an Organization.  An Organization may have multiple user associations.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
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
class OrganizationUserAssociations extends SortableAssociations
{
    use Accessor;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->organizationUserAssociationsCacheDuration;
        $this->cacheDependency = $settings->organizationUserAssociationsCacheDependency;

        parent::init();
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
     * @return string
     */
    const SORT_ORDER_ATTRIBUTE = 'organizationOrder';

    /**
     * @inheritdoc
     */
    protected static function tableAlias(): string
    {
        return UserAssociation::tableAlias();
    }

    /**
     * @inheritdoc
     * @return OrganizationUserAssociationQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        /** @var OrganizationUserAssociationQuery $query */
        $query = Craft::createObject(
            OrganizationUserAssociationQuery::class,
            [UserAssociation::class]
        );

        QueryHelper::configure(
            $query,
            $this->prepareQueryConfig($config)
        );

        return $query;
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
     * @return SortableAssociationQueryInterface|UserOrganizationAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->{static::SOURCE_ATTRIBUTE}
        );
    }

    /**
     * @param SortableAssociationQueryInterface|UserOrganizationAssociationQuery $query
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
     * @return SortableAssociationQueryInterface|UserOrganizationAssociationQuery
     */
    private function query(
        $source
    ): SortableAssociationQueryInterface {
        /** @var UserOrganizationAssociationQuery $query */
        $query = $this->getQuery();
        return $query->where([
            static::SOURCE_ATTRIBUTE => $source
        ])
            ->orderBy([static::SORT_ORDER_ATTRIBUTE => SORT_ASC]);
    }

    /**
     * @param $source
     * @return array
     */
    private function associations(
        $source
    ): array {
        /** @var UserOrganizationAssociationQuery $query */
        $query = $this->query($source);
        return $query->indexBy(static::TARGET_ATTRIBUTE)
            ->all();
    }
}
