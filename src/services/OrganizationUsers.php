<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\elements\User as UserElement;
use craft\helpers\ArrayHelper;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserAssociation;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationUsers extends Component
{
    /**
     * @param OrganizationElement $organization
     * @param UserQuery $query
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        OrganizationElement $organization,
        UserQuery $query
    ): bool {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = OrganizationPlugin::getInstance()->getOrganizationUserAssociations();

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $organization->getId() ?: false
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $organization->getId())
        );

        return $associationService->save($query);
    }

    /**
     * @param OrganizationElement $organization
     * @param UserQuery $query
     * @return bool
     * @throws \Exception
     */
    public function dissociate(
        OrganizationElement $organization,
        UserQuery $query
    ): bool {
        return $this->associations(
            $organization,
            $query,
            [
                OrganizationPlugin::getInstance()->getOrganizationUserAssociations(),
                'dissociate'
            ]
        );
    }

    /**
     * @param OrganizationElement $organization
     * @param UserQuery $query
     * @return bool
     * @throws \Exception
     */
    public function associate(
        OrganizationElement $organization,
        UserQuery $query
    ): bool {
        return $this->associations(
            $organization,
            $query,
            [
                OrganizationPlugin::getInstance()->getOrganizationUserAssociations(),
                'associate'
            ]
        );
    }

    /**
     * @param OrganizationElement $organization
     * @param UserQuery $query
     * @param callable $callable
     * @return bool
     */
    protected function associations(OrganizationElement $organization, UserQuery $query, callable $callable)
    {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $models = ArrayHelper::index($models, 'id');

        $success = true;
        $ids = [];
        $count = count($models);
        $i = 0;
        foreach ($this->toAssociations($models, $organization->getId()) as $association) {
            if (true === call_user_func_array($callable, [$association, ++$i === $count])) {
                ArrayHelper::remove($models, $association->userId);
                $ids[] = $association->userId;
                continue;
            }

            $success = false;
        }

        $query->id($ids);

        if ($success === false) {
            $query->setCachedResult($models);
        }

        return $success;
    }

    /**
     * @param UserElement[] $users
     * @param int $organizationId
     * @return UserAssociation[]
     */
    protected function toAssociations(
        array $users,
        int $organizationId
    ) {
        $associations = [];
        $sortOrder = 1;

        $associationService = OrganizationPlugin::getInstance()->getOrganizationUserAssociations();
        $sortOrderAttribute = $associationService::SORT_ORDER_ATTRIBUTE;

        $existingAssociations = $associationService->findAllByCriteria([
            'where' => [
                $associationService::TARGET_ATTRIBUTE => ArrayHelper::getColumn($users, 'id'),
                $associationService::SOURCE_ATTRIBUTE => $organizationId,
            ],
            'indexBy' => $associationService::TARGET_ATTRIBUTE
        ]);

        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($existingAssociations, $user->getId()))) {
                $association = $associationService->create([
                    'userId' => (int)$user->getId(),
                    'organizationId' => (int)$organizationId
                ]);
            }

            $association->{$sortOrderAttribute} = $sortOrder++;
            $associations[] = $association;
        }

        return $associations;
    }
}
