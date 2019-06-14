<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\OrganizationTypeAssociationQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;
use Tightenco\Collect\Support\Collection;

/**
 * Manages Organization Types associated to Organizations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @method OrganizationTypeAssociation findOrCreate($object)
 * @method OrganizationTypeAssociation findOne($object = null)
 * @method OrganizationTypeAssociation findOrFail($object)
 */
class OrganizationTypeRelationship implements RelationshipInterface
{
    use RelationshipTrait;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }


    /************************************************************
     * COLLECTION
     ************************************************************/

    /**
     * Get an array of types associated to an organization
     *
     * @return OrganizationType[]|Collection
     */
    public function getCollection(): Collection
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = OrganizationType::find()
            ->organization($this->organization)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ]);

        if (null === $this->collection) {
            return Collection::make(
                $query->all()
            );
        };

        return Collection::make(
            $query
                ->id($this->collection->sortBy('sortOrder')->pluck('typeId'))
                ->limit(null)
                ->all()
        );
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @param array $criteria
     * @return OrganizationTypeAssociationQuery
     */
    protected function query(array $criteria = []): OrganizationTypeAssociationQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = OrganizationTypeAssociation::find()
            ->setOrganizationId($this->organization->getId() ?: false)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|string $type
     * @return OrganizationTypeAssociation
     */
    protected function create($type): OrganizationTypeAssociation
    {
        return (new OrganizationTypeAssociation())
            ->setOrganization($this->organization)
            ->setType($this->resolveType($type));
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function associationDelta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('typeId')
            ->all();

        $associations = [];
        $order = 1;
        /** @var OrganizationTypeAssociation $newAssociation */
        foreach ($this->getRelationships()->sortBy('sortOrder') as $newAssociation) {
            if (null === ($association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getTypeId()
            ))) {
                $association = $newAssociation;
            }

            $association->sortOrder = $order++;

            $associations[] = $association;
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @inheritDoc
     */
    protected function handleAssociationError()
    {
        $this->organization->addError('types', 'Unable to save organization types.');
    }


    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array|null $type
     * @return int|null
     */
    protected function findKey($type = null)
    {
        if (null === ($record = $this->resolveType($type))) {
            Organizations::info(sprintf(
                "Unable to resolve organization type: %s",
                (string)Json::encode($type)
            ));
            return null;
        }

        foreach ($this->findAll() as $key => $association) {
            if ($association->getTypeId() == $record->id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array|null $type
     * @return OrganizationType|null
     */
    protected function resolveType($type = null)
    {
        if (null === $type) {
            return null;
        }

        if ($type instanceof OrganizationTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof OrganizationType) {
            return $type;
        }

        if (is_array($type) &&
            null !== ($id = ArrayHelper::getValue($type, 'id'))
        ) {
            $type = ['id' => $id];
        }

        return OrganizationType::findOne($type);
    }
}
