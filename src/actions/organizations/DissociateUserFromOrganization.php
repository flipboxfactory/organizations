<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use flipbox\craft\ember\actions\records\DeleteRecordTrait;
use flipbox\organizations\records\UserAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class DissociateUserFromOrganization extends Action
{
    use DeleteRecordTrait, LookupAssociationTrait;

    /**
     * @param string $user
     * @param string $organization
     * @return null|\yii\base\Model|\yii\web\Response
     * @throws HttpException
     */
    public function run(
        string $user,
        string $organization
    ) {
        if (null === ($user = $this->findUser($user))) {
            return $this->handleNotFoundResponse();
        }

        if (null === ($organization = $this->findOrganization($organization))) {
            return $this->handleNotFoundResponse();
        }

        return $this->runInternal(
            $organization->getUsers()->findOrCreate($user)
        );
    }

    /**
     * @inheritdoc
     * @param UserAssociation $record
     * @return bool
     */
    protected function performAction(UserAssociation $association): bool
    {
        return $association->save();
    }
}
