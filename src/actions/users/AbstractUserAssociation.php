<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/domains/license
 * @link       https://www.flipboxfactory.com/software/domains/
 */

namespace flipbox\organizations\actions\users;

use Craft;
use craft\elements\User;
use flipbox\craft\ember\actions\LookupTrait;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\records\UserAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since  1.0.0
 */
abstract class AbstractUserAssociation extends Action
{
    use LookupTrait,
        ManageTrait;

    /**
     * @inheritdoc
     * @param UserAssociation $record
     * @return bool
     */
    abstract protected function performAction(User $user, Organization $organization, int $sortOrder = null): bool;

    /**
     * @param string|int $identifier
     * @return User|null
     */
    protected function find($identifier)
    {
        if (is_numeric($identifier)) {
            return Craft::$app->getUsers()->getUserById($identifier);
        }

        return Craft::$app->getUsers()->getUserByUsernameOrEmail($identifier);
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
    )
    {
        if (null === ($user = $this->find($user))) {
            return $this->handleNotFoundResponse();
        }

        if (null === ($organization = Organization::findOne($organization))) {
            return $this->handleNotFoundResponse();
        }

        return $this->runInternal($user, $organization, $sortOrder);
    }

    /**
     * @param User $user
     * @param Organization $organization
     * @param int|null $sortOrder
     * @return mixed
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(User $user, Organization $organization, int $sortOrder = null)
    {
        // Check access
        if (($access = $this->checkAccess($user, $organization, $sortOrder)) !== true) {
            return $access;
        }

        if (!$this->performAction($user, $organization, $sortOrder)) {
            return $this->handleFailResponse($user);
        }

        return $this->handleSuccessResponse($user);
    }
}
