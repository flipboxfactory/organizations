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
use flipbox\organizations\db\OrganizationQuery;
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
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->usersCacheDuration;
        $this->cacheDependency = $settings->usersCacheDependency;

        parent::init();
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
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        /** @var UserElement[] $models */
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $models = $query->all();

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
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @return bool
     * @throws \Exception
     */
    public function dissociate(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        return $this->associations(
            $query,
            $user,
            [
                OrganizationPlugin::getInstance()->getUserOrganizationAssociations(),
                'dissociate'
            ]
        );
    }

    /**
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @return bool
     * @throws \Exception
     */
    public function associate(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        return $this->associations(
            $query,
            $user,
            [
                OrganizationPlugin::getInstance()->getUserOrganizationAssociations(),
                'associate'
            ]
        );
    }

    /**
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @param callable $callable
     * @return bool
     */
    protected function associations(
        OrganizationQuery $query,
        UserElement $user,
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
    private function toAssociations(
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
                    'userId' => (int) $userId,
                    'organizationId' => (int) $organization->getId()
                ]);
            }

            $association->{$sortOrderAttribute} = $sortOrder++;
            $associations[] = $association;
        }

        return $associations;
    }
}
