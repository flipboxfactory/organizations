<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\ember\exceptions\RecordNotFoundException;
use flipbox\ember\helpers\SiteHelper;
use flipbox\ember\services\traits\elements\MultiSiteAccessor;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use craft\elements\User as UserElement;
use flipbox\organizations\records\UserAssociation;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationElement create($config = [])
 * @method OrganizationElement find($identifier, int $siteId = null)
 * @method OrganizationElement get($identifier, int $siteId = null)
 * @method OrganizationQuery getQuery($criteria = [])
 */
class Organizations extends Component
{
    use MultiSiteAccessor;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->organizationsCacheDuration;
        $this->cacheDependency = $settings->organizationsCacheDependency;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function elementClass(): string
    {
        return OrganizationElement::class;
    }

    /**
     * @param $identifier
     * @param int|null $siteId
     * @return array
     */
    protected function identifierCondition($identifier, int $siteId = null): array
    {
        $base = [
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'status' => null
        ];

        if (is_array($identifier)) {
            return array_merge($base, $identifier);
        }

        if (!is_numeric($identifier) && is_string($identifier)) {
            $base['slug'] = $identifier;
        } else {
            $base['id'] = $identifier;
        }

        return $base;
    }

    /**
     * @param mixed $organization
     * @return OrganizationElement
     */
    public function resolve($organization)
    {
        if (is_array($organization) &&
            null !== ($id = ArrayHelper::getValue($organization, 'id'))
        ) {
            return $this->get($id);
        }

        if ($object = $this->find($organization)) {
            return $object;
        }

        return $this->create($organization);
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

        $models = $query->all();

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
                OrganizationPlugin::getInstance()->getOrganizationUserAssociations(),
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
                OrganizationPlugin::getInstance()->getOrganizationUserAssociations(),
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
                    'userId' => (int) $user->getId(),
                    'organizationId' => (int) $organizationId
                ]);
            }

            $association->{$sortOrderAttribute} = $sortOrder++;
            $associations[] = $association;
        }

        return $associations;
    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws RecordNotFoundException
     */
    protected function recordNotFoundException()
    {
        throw new RecordNotFoundException('Record does not exist.');
    }
}
