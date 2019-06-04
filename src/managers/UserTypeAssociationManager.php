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
use flipbox\organizations\Organizations;
use flipbox\organizations\queries\UserTypeAssociationQuery;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;

/**
 * Manages User Types associated to Organization/User associations
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 *
 * @property UserTypeAssociation[] $associations
 *
 * @method UserTypeAssociation findOrCreate($object)
 * @method UserTypeAssociation[] findAll()
 * @method UserTypeAssociation findOne($object = null)
 * @method UserTypeAssociation findOrFail($object)
 */
class UserTypeAssociationManager
{
    use AssociationManagerTrait {
        reset as parentRest;
        setCache as parentSetCache;
        addToCache as parentAddToCache;
        removeFromCache as parentRemoveFromCache;
    }

    /**
     * @var UserAssociation
     */
    private $association;

    /**
     * @param UserAssociation $association
     */
    public function __construct(UserAssociation $association)
    {
        $this->association = $association;
    }

    /**
     * @return UserTypeAssociationQuery
     */
    public function query(): UserTypeAssociationQuery
    {
        $query = UserTypeAssociation::find()
            ->setUserId($this->association->getId() ?: false)
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
     * @param UserTypeAssociation|UserType|int|string $type
     * @return UserTypeAssociation
     */
    public function create($type): UserTypeAssociation
    {
        $association = (new UserTypeAssociation())
            ->setType($this->resolveType($type));

        $association->userId = $this->association->id;

        return $association;
    }


    /**
     * Reset associations
     */
    public function reset()
    {
        unset($this->association->types);
        return $this->parentRest();
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
                $newAssociation->typeId
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
        $this->association->addError('types', 'Unable to save organization types.');
    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param array $associations
     * @return static
     */
    protected function setCache(array $associations): self
    {
        $this->parentSetCache($associations);
        $this->syncToRelations();

        return $this;
    }

    /**
     * @param $association
     * @return static
     */
    protected function addToCache($association): self
    {
        $this->parentAddToCache($association);
        $this->syncToRelations();

        return $this;
    }

    /**
     * @param int $key
     * @return static
     */
    protected function removeFromCache(int $key): self
    {
        $this->parentRemoveFromCache($key);
        $this->syncToRelations();

        return $this;
    }

    /*******************************************
     * UTILS
     *******************************************/

    /**
     * @return $this
     */
    protected function syncToRelations()
    {
        $this->association->populateRelation('types', ArrayHelper::getColumn(
            $this->findAll(),
            'type'
        ));
        return $this;
    }

    /**
     * @param UserTypeAssociation|UserType|int|array|null $type
     * @return int|null
     */
    protected function findKey($type = null)
    {
        if (null === ($record = $this->resolveType($type))) {
            Organizations::info(sprintf(
                "Unable to resolve user association type: %s",
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
     * @param UserTypeAssociation|UserType|int|array|null $type
     * @return UserType|null
     */
    protected function resolveType($type = null)
    {
        if (null === $type) {
            return null;
        }

        if ($type instanceof UserTypeAssociation) {
            return $type->getType();
        }

        if ($type instanceof UserType) {
            return $type;
        }

        if (is_array($type) &&
            null !== ($id = ArrayHelper::getValue($type, 'id'))
        ) {
            $type = ['id' => $id];
        }

        return UserType::findOne($type);
    }
}
