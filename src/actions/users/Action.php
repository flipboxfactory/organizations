<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/domains/license
 * @link       https://www.flipboxfactory.com/software/domains/
 */

namespace flipbox\organizations\actions\users;

use craft\elements\User;
use flipbox\ember\actions\model\traits\Lookup;
use flipbox\ember\actions\model\traits\Manage;
use flipbox\ember\exceptions\RecordNotFoundException;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;
use yii\base\Model;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since  1.0.0
 */
abstract class Action extends \yii\base\Action
{
    use Lookup,
        Manage;

    /**
     * @param Model $model
     * @return bool
     * @throws RecordNotFoundException
     */
    protected function ensureUserAssociation(Model $model): bool
    {
        if (!$model instanceof UserAssociation) {
            throw new RecordNotFoundException(sprintf(
                "User Association must be an instance of '%s', '%s' given.",
                UserAssociation::class,
                get_class($model)
            ));
        }

        return true;
    }

    /**
     * @param string|int $identifier
     * @return User|null
     */
    protected function find($identifier)
    {
        return Organizations::getInstance()->getUsers()->find($identifier);
    }

    /**
     * @param string $user
     * @param string $organization
     * @param int|null $sortOrder
     * @return null|\yii\base\Model|\yii\web\Response
     * @throws HttpException
     */
    public function run(
        string $user,
        string $organization,
        int $sortOrder = null
    ) {
        if (null === ($user = $this->find($user))) {
            return $this->handleNotFoundResponse();
        }

        $organization = Organization::findOne([
            is_numeric($organization) ? 'id' : 'slug' => $organization
        ]);

        if (null === $organization) {
            return $this->handleNotFoundResponse();
        }

        $model = new UserAssociation([
            'userId' => $user->getId(),
            'organizationId' => $organization->getId(),
            Organizations::getInstance()->getUserOrganizationAssociations()::SORT_ORDER_ATTRIBUTE => $sortOrder
        ]);

        return $this->runInternal($model);
    }
}
