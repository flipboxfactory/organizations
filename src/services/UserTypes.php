<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\db\Query;
use craft\elements\User as UserElement;
use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\services\traits\records\AccessorByString;
use flipbox\organizations\db\UserTypeQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserType create(array $attributes = [])
 * @method UserType find($identifier)
 * @method UserType get($identifier)
 * @method UserType findByString($identifier)
 * @method UserType getByString($identifier)
 * @method UserType findByCondition($condition = [])
 * @method UserType getByCondition($condition = [])
 * @method UserType findByCriteria($criteria = [])
 * @method UserType getByCriteria($criteria = [])
 * @method UserType[] findAll(string $toScenario = null)
 * @method UserType[] findAllByCondition($condition = [])
 * @method UserType[] getAllByCondition($condition = [])
 * @method UserType[] findAllByCriteria($criteria = [])
 * @method UserType[] getAllByCriteria($criteria = [])
 * @method UserTypeQuery getQuery($config = []): ActiveQuery($criteria = [])
 */
class UserTypes extends Component
{
    use AccessorByString;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->userTypesCacheDuration;
        $this->cacheDependency = $settings->userTypesCacheDependency;

        parent::init();
    }

    /**
     * @return string
     */
    protected static function stringProperty(): string
    {
        return 'handle';
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return UserType::class;
    }

    /**
     * @param mixed $type
     * @return UserType
     */
    public function resolve($type)
    {
        if ($type = $this->find($type)) {
            return $type;
        }

        $type = ArrayHelper::toArray($type, [], false);

        try {
            $object = $this->create($type);
        } catch (\Exception $e) {
            $object = new UserType();
            ObjectHelper::populate(
                $object,
                $type
            );
        }

        return $object;
    }

    /**
     * @param UserTypeQuery $query
     * @param UserElement $user
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        UserTypeQuery $query,
        UserElement $user,
        OrganizationElement $organization
    ): bool {
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = OrganizationsPlugin::getInstance()->getUserTypeAssociations();

        $userAssociationId = $this->associationId($user->getId(), $organization->getId());

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $userAssociationId
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $userAssociationId)
        );

        return $associationService->save($query);
    }


    /*******************************************
     * USER / ORGANIZATION ASSOCIATION ID
     *******************************************/

    /**
     * @param array $types
     * @param int $userAssociationId
     * @return array
     */
    private function toAssociations(
        array $types,
        int $userAssociationId
    ) {
        $associations = [];
        foreach ($types as $type) {
            $associations[] = new UserTypeAssociation([
                'typeId' => $type->id,
                'userId' => $userAssociationId
            ]);
        }

        return $associations;
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return Query
     */
    private function associationIdQuery(int $userId, int $organizationId): Query
    {
        return (new Query())
            ->select(['id'])
            ->from([UserAssociation::tableName()])
            ->where([
                'organizationId' => $organizationId,
                'userId' => $userId,
            ]);
    }

    /**
     * @param int $userId
     * @param int $organizationId
     * @return string|null
     */
    private function associationId(int $userId, int $organizationId)
    {
        $id = $this->associationIdQuery($userId, $organizationId)->scalar();
        return is_string($id) ? $id : null;
    }
}
