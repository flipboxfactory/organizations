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
        return $this->getRelationships()
            ->sortBy('sortOrder')
            ->pluck('type');
    }

    /**
     * @return Collection
     */
    protected function existingRelationships(): Collection
    {
        return new Collection(
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
    protected function query(): OrganizationTypeAssociationQuery
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
        return (new OrganizationTypeAssociation())
            ->setOrganization($this->organization)
            ->setType($this->resolve($type));
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
        if (null === ($record = $this->resolve($type))) {
            Organizations::info(sprintf(
                "Unable to resolve organization type: %s",
                (string)Json::encode($type)
            ));
            return null;
        }

        /** @var OrganizationTypeAssociation $association */
        foreach ($this->getRelationships()->all() as $key => $association) {
            if ($association->getTypeId() == $record->id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param OrganizationTypeAssociation|OrganizationType|int|array $type
     * @return OrganizationType|null
     */
    protected function resolveObject($type)
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
