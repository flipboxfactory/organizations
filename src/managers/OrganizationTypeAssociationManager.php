<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\managers;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\OrganizationTypeAssociationQuery;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;

/**
 * Manages Organization Types associated to Organizations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 *
 * @property OrganizationTypeAssociation[] $associations
 *
 * @method OrganizationTypeAssociation findOrCreate($object)
 * @method OrganizationTypeAssociation[] findAll()
 * @method OrganizationTypeAssociation findOne($object = null)
 * @method OrganizationTypeAssociation findOrFail($object)
 */
class OrganizationTypeAssociationManager
{
    use AssociationManagerTrait;

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

    /**
     * @param array $criteria
     * @return OrganizationTypeAssociationQuery
     */
    public function query(array $criteria = []): OrganizationTypeAssociationQuery
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
    public function create($type): OrganizationTypeAssociation
    {
        return (new OrganizationTypeAssociation())
            ->setOrganization($this->organization)
            ->setType($this->resolveType($type));
    }


    /**
     * Reset associations
     */
    public function reset()
    {
        $this->associations = null;
        $this->mutated = false;
        return $this;
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
        foreach ($this->findAll() as $newAssociation) {
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
