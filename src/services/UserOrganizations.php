<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\elements\db\UserQuery;
use craft\elements\User as UserElement;
use craft\helpers\ArrayHelper;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserAssociation;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserElement parentFind($identifier)
 * @method UserElement create($config = [])
 * @method UserElement get($identifier)
 * @method UserQuery getQuery($criteria = [])
 */
class UserOrganizations extends Component
{
    /**
     * @param UserElement $user
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        UserElement $user,
        OrganizationQuery $query
    ): bool {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = OrganizationPlugin::getInstance()->getUserOrganizationAssociations();

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $user->getId() ?: false
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $user->getId())
        );

        return $associationService->save($query);
    }

    /**
     * @param UserElement $user
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Exception
     */
    public function dissociate(
        UserElement $user,
        OrganizationQuery $query
    ): bool {
        return $this->associations(
            $user,
            $query,
            [
                OrganizationPlugin::getInstance()->getUserOrganizationAssociations(),
                'dissociate'
            ]
        );
    }

    /**
     * @param UserElement $user
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Exception
     */
    public function associate(
        UserElement $user,
        OrganizationQuery $query
    ): bool {
        return $this->associations(
            $user,
            $query,
            [
                OrganizationPlugin::getInstance()->getUserOrganizationAssociations(),
                'associate'
            ]
        );
    }

    /**
     * @param UserElement $user
     * @param OrganizationQuery $query
     * @param callable $callable
     * @return bool
     */
    protected function associations(
        UserElement $user,
        OrganizationQuery $query,
        callable $callable
    ) {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $models = ArrayHelper::index($models, 'id');

        $success = true;
        $ids = [];
        $count = count($models);
        $i = 0;
        foreach ($this->toAssociations($models, $user->getId()) as $association) {
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
     * @param OrganizationElement[] $organizations
     * @param int $userId
     * @return UserAssociation[]
     */
    protected function toAssociations(
        array $organizations,
        int $userId
    ) {
        $associations = [];
        $sortOrder = 1;

        $associationService = OrganizationPlugin::getInstance()->getUserOrganizationAssociations();
        $sortOrderAttribute = $associationService::SORT_ORDER_ATTRIBUTE;

        $existingAssociations = $associationService->findAllByCriteria([
            'where' => [
                $associationService::SOURCE_ATTRIBUTE => ArrayHelper::getColumn($organizations, 'id'),
                $associationService::TARGET_ATTRIBUTE => $userId,
            ],
            'indexBy' => $associationService::SOURCE_ATTRIBUTE
        ]);

        foreach ($organizations as $organization) {
            if (null === ($association = ArrayHelper::remove($existingAssociations, $organization->getId()))) {
                $association = $associationService->create([
                    'userId' => (int)$userId,
                    'organizationId' => (int)$organization->getId()
                ]);
            }

            $association->{$sortOrderAttribute} = $sortOrder++;
            $associations[] = $association;
        }

        return $associations;
    }
}
