<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use Craft;
use craft\elements\User;
use flipbox\organizations\elements\Organization;
use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 *
 * @method ActiveRecord populate(ActiveRecord $record)
 */
trait LookupAssociationTrait
{
    /**
     * HTTP not found response code
     *
     * @return int
     */
    protected function statusCodeNotFound(): int
    {
        return $this->statusCodeNotFound ?? 404;
    }

    /**
     * @return string
     */
    protected function messageNotFound(): string
    {
        return $this->messageNotFound ?? 'Unable to find object.';
    }

    /**
     * @return null
     * @throws HttpException
     */
    protected function handleNotFoundResponse()
    {
        throw new HttpException(
            $this->statusCodeNotFound(),
            $this->messageNotFound()
        );
    }

    /**
     * @param string|int $identifier
     * @return ORganization|null
     */
    protected function findOrganization($identifier)
    {
        return Organization::findOne($identifier);
    }

    /**
     * @param string|int $identifier
     * @return User|null
     */
    protected function findUser($identifier)
    {
        if (is_numeric($identifier)) {
            return Craft::$app->getUsers()->getUserById($identifier);
        }

        return Craft::$app->getUsers()->getUserByUsernameOrEmail($identifier);
    }
}
