<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use craft\elements\db\UserQuery;
use craft\elements\User as UserElement;
use craft\helpers\ArrayHelper;
use flipbox\ember\services\traits\elements\Accessor;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserAssociation;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserElement parentFind($identifier)
 */
class Users extends Component
{
    use Accessor {
        find as parentFind;
    }

    /**
     * @inheritdoc
     */
    public static function elementClass(): string
    {
        return UserElement::class;
    }

    /**
     * @param $identifier
     * @return array
     */
    protected function identifierCondition($identifier): array
    {
        $base = [
            'status' => null
        ];

        if (is_array($identifier)) {
            return array_merge($base, $identifier);
        }

        if (!is_numeric($identifier) && is_string($identifier)) {
            $base['where'] = [
                'or',
                ['username' => $identifier],
                ['email' => $identifier]
            ];
        } else {
            $base['id'] = $identifier;
        }

        return $base;
    }

    /**
     * @param mixed $user
     * @return UserElement
     * @throws InvalidConfigException
     */
    public function resolve($user = 'CURRENT_USER')
    {
        if (is_array($user) &&
            null !== ($id = ArrayHelper::getValue($user, 'id'))
        ) {
            return Craft::$app->getUsers()->getUserById($id);
        }

        if ($object = $this->find($user)) {
            return $object;
        }

        return $this->create($user);
    }

    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param mixed $identifier
     * @return UserElement|null
     */
    public function find($identifier = 'CURRENT_USER')
    {
        if ('CURRENT_USER' === $identifier) {
            return Craft::$app->getUser()->getIdentity();
        }

        return $this->parentFind($identifier);
    }

    /**
     * @param UserQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        UserQuery $query,
        OrganizationElement $organization
    ): bool {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = OrganizationPlugin::getInstance()->getUserAssociations();

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $organization->getId() ?: false
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $organization->getId())
        );

        return $associationService->save($query);
    }

    /**
     * @param UserQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function dissociate(
        UserQuery $query,
        OrganizationElement $organization
    ): bool {
        return $this->associations(
            $query,
            $organization,
            [
                OrganizationPlugin::getInstance()->getUserAssociations(),
                'dissociate'
            ]
        );
    }

    /**
     * @param UserQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function associate(
        UserQuery $query,
        OrganizationElement $organization
    ): bool {
        return $this->associations(
            $query,
            $organization,
            [
                OrganizationPlugin::getInstance()->getUserAssociations(),
                'associate'
            ]
        );
    }

    /**
     * @param UserQuery $query
     * @param OrganizationElement $organization
     * @param callable $callable
     * @return bool
     */
    protected function associations(UserQuery $query, OrganizationElement $organization, callable $callable)
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
    private function toAssociations(
        array $users,
        int $organizationId
    ) {
        $associations = [];
        $sortOrder = 1;
        foreach ($users as $user) {
            $associations[] = new UserAssociation([
                'organizationId' => $organizationId,
                'userId' => $user->getId(),
                'sortOrder' => $sortOrder++
            ]);
        }

        return $associations;
    }
}
