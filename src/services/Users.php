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
use flipbox\organizations\Organizations as OrganizationPlugin;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserElement parentFind($identifier)
 * @method UserElement create($config = [])
 * @method UserElement get($identifier)
 * @method UserQuery getQuery($criteria = [])
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
     * @param string $user
     * @return UserElement|null
     * @throws InvalidConfigException
     */
    public function resolve($user = 'CURRENT_USER')
    {
        if (is_array($user) &&
            null !== ($id = ArrayHelper::getValue($user, 'id'))
        ) {
            return $this->find($id);
        }

        if (null !== ($object = $this->find($user))) {
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
     *
     * @deprecated
     */
    public function saveAssociations(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        return OrganizationPlugin::getInstance()->getUserOrganizations()->saveAssociations($user, $query);
    }

    /**
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @return bool
     * @throws \Exception
     *
     * @deprecated
     */
    public function dissociate(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        return OrganizationPlugin::getInstance()->getUserOrganizations()->dissociate($user, $query);
    }

    /**
     * @param OrganizationQuery $query
     * @param UserElement $user
     * @return bool
     * @throws \Exception
     *
     * @deprecated
     */
    public function associate(
        OrganizationQuery $query,
        UserElement $user
    ): bool {
        return OrganizationPlugin::getInstance()->getUserOrganizations()->associate($user, $query);
    }
}
