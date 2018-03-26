<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\controllers;

use Craft;
use flipbox\ember\helpers\ArrayHelper;
use flipbox\organization\actions\users\Associate;
use flipbox\organization\actions\users\Dissociate;

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

        /** @var Associate $action */
        $action = Craft::createObject([
            'class' => Associate::class,
            'checkAccess' => [$this, 'checkAssociateAccess']
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

        /** @var Dissociate $action */
        $action = Craft::createObject([
            'class' => Dissociate::class,
            'checkAccess' => [$this, 'checkDissociateAccess']
        ], [
            'dissociate',
            $this
        ]);

        return $action->runWithParams([
            'organization' => $organization,
            'user' => $user
        ]);
    }

    /**
     * @return bool
     */
    public function checkAssociateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    public function checkDissociateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    protected function checkAdminAccess()
    {
        $this->requireLogin();
        return Craft::$app->getUser()->getIsAdmin();
    }
}
