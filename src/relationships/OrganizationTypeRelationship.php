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
use flipbox\organizations\records\UserAssociation;
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
        return $this->getRelationships()
            ->pluck('type');
    }

    /**
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        return $this->createRelations(
            $this->query()
                ->with('typeRecord')
                ->all()
        );
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return OrganizationTypeAssociationQuery
     */
    private function query(): OrganizationTypeAssociationQuery
    {
        return OrganizationTypeAssociation::find()
            ->setOrganizationId($this->organization->getId() ?: false)
            ->orderBy([
                'sortOrder' => SORT_ASC
            ])
            ->limit(null);
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|string $type
     * @return OrganizationTypeAssociation
     */
    protected function create($type): OrganizationTypeAssociation
    {
        if ($type instanceof OrganizationTypeAssociation) {
            return $type;
        }

        return (new OrganizationTypeAssociation())
            ->setOrganization($this->organization)
            ->setType($this->resolveObject($type));
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function delta(): array
    {
        $existingAssociations = $this->query()
            ->indexBy('typeId')
            ->all();

        $associations = [];
        $order = 1;

        /** @var OrganizationTypeAssociation $newAssociation */
        foreach ($this->getRelationships() as $newAssociation) {
            $association = ArrayHelper::remove(
                $existingAssociations,
                $newAssociation->getTypeId()
            );

            $newAssociation->sortOrder = $order++;

            /** @var OrganizationTypeAssociation $association */
            $association = $association ?: $newAssociation;

            // Has anything changed?
            if (!$association->getIsNewRecord() && !$this->hasChanged($newAssociation, $association)) {
                continue;
            }

            $associations[] = $this->sync($association, $newAssociation);
        }

        return [$associations, $existingAssociations];
    }

    /**
     * @param OrganizationTypeAssociation $new
     * @param OrganizationTypeAssociation $existing
     * @return bool
     */
    private function hasChanged(OrganizationTypeAssociation $new, OrganizationTypeAssociation $existing): bool
    {
        return $new->sortOrder != $existing->sortOrder;
    }

    /**
     * @param OrganizationTypeAssociation $from
     * @param OrganizationTypeAssociation $to
     *
     * @return OrganizationTypeAssociation
     */
    private function sync(
        OrganizationTypeAssociation $to,
        OrganizationTypeAssociation $from
    ): OrganizationTypeAssociation {
        $to->sortOrder = $from->sortOrder;

        $to->ignoreSortOrder();

        return $to;
    }


    /*******************************************
     * COLLECTION UTILS
     *******************************************/

    /**
     * @inheritDoc
     */
    protected function insertCollection(Collection $collection, OrganizationTypeAssociation $association)
    {
        if ($association->sortOrder > 0) {
            $collection->splice($association->sortOrder - 1, 0, [$association]);
            return;
        }

        $collection->push($association);
    }

    /**
     * @inheritDoc
     */
    protected function updateCollection(Collection $collection, OrganizationTypeAssociation $association)
    {
        if (null !== ($key = $this->findKey($association))) {
            $collection->offsetUnset($key);
        }

        $this->insertCollection($collection, $association);
    }


    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array|null $object
     * @return int|null
     */
    protected function findKey($object = null)
    {
        if ($object instanceof OrganizationTypeAssociation) {
            return $this->findRelationshipKey($object->getTypeId());
        }

        if (null === ($type = $this->resolveObject($object))) {
            return null;
        }

        return $this->findRelationshipKey($type->id);
    }

    /**
     * @param $identifier
     * @return int|string|null
     */
    protected function findRelationshipKey($identifier)
    {
        if (null === $identifier) {
            return null;
        }

        /** @var OrganizationTypeAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->getTypeId() == $identifier) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array $type
     * @return OrganizationType|null
     */
    protected function resolveObjectInternal($type)
    {
        if ($type instanceof OrganizationTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof OrganizationType) {
            return $type;
        }

        return OrganizationType::findOne($type);
    }
}
