<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\organizations\actions\users\AssociateUserToOrganization;
use flipbox\organizations\actions\users\DissociateUserFromOrganization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UsersController extends AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'error' => [
                    'default' => 'organization'
                ],
                'redirect' => [
                    'only' => ['associate', 'dissociate'],
                    'actions' => [
                        'associate' => [204],
                        'dissociate' => [204],
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'associate' => [
                            204 => Craft::t('organizations', "Successfully associated user."),
                            401 => Craft::t('organizations', "Failed to associate user.")
                        ],
                        'dissociate' => [
                            204 => Craft::t('organizations', "Successfully dissociated user."),
                            401 => Craft::t('organizations', "Failed to dissociate user.")
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return [
            'associate' => ['post']
        ];
    }

    /**
     * @param int|string|null $user
     * @param int|string|null $organization
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAssociate($user = null, $organization = null)
    {
        if (null === $organization) {
            $organization = Craft::$app->getRequest()->getBodyParam('organization');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam('user');
        }

        /** @var AssociateUserToOrganization $action */
        $action = Craft::createObject([
            'class' => AssociateUserToOrganization::class
        ], [
            'associate',
            $this
        ]);

        return $action->runWithParams([
            'organization' => $organization,
            'user' => $user
        ]);
    }

    /**
     * @param int|string|null $user
     * @param int|string|null $organization
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDissociate($user = null, $organization = null)
    {
        if (null === $organization) {
            $organization = Craft::$app->getRequest()->getBodyParam('organization');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam('user');
        }

        /** @var DissociateUserFromOrganization $action */
        $action = Craft::createObject([
            'class' => DissociateUserFromOrganization::class
        ], [
            'dissociate',
            $this
        ]);

        return $action->runWithParams([
            'organization' => $organization,
            'user' => $user
        ]);
    }
}
