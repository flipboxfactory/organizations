<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\settings;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\organizations\actions\users\CreateUserType;
use flipbox\organizations\actions\users\DeleteUserType;
use flipbox\organizations\actions\users\UpdateUserType;
use flipbox\organizations\cp\controllers\AbstractController;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserTypesController extends AbstractController
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
                    'default' => 'userType'
                ],
                'redirect' => [
                    'only' => ['create', 'update', 'delete'],
                    'actions' => [
                        'create' => [201],
                        'update' => [200],
                        'delete' => [204],
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'create' => [
                            201 => Craft::t('organizations', "User Type successfully created."),
                            401 => Craft::t('organizations', "Failed to create User Type.")
                        ],
                        'update' => [
                            200 => Craft::t('organizations', "User Type successfully updated."),
                            401 => Craft::t('organizations', "Failed to update User Type.")
                        ],
                        'delete' => [
                            204 => Craft::t('organizations', "User Type successfully deleted."),
                            401 => Craft::t('organizations', "Failed to delete User Type.")
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
            'create' => ['post'],
            'update' => ['post', 'put'],
            'delete' => ['post', 'delete']
        ];
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        /** @var CreateUserType $action */
        $action = Craft::createObject([
            'class' => CreateUserType::class
        ], [
            'create',
            $this
        ]);

        $response = $action->runWithParams([]);

        return $response;
    }

    /**
     * @param string|int|null $type
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($type = null)
    {
        if (null === $type) {
            $type = Craft::$app->getRequest()->getBodyParam('type');
        }

        /** @var UpdateUserType $action */
        $action = Craft::createObject([
            'class' => UpdateUserType::class
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([
            'type' => $type
        ]);
    }

    /**
     * @param string|int|null $type
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelete($type = null)
    {
        if (null === $type) {
            $type = Craft::$app->getRequest()->getBodyParam('type');
        }

        /** @var DeleteUserType $action */
        $action = Craft::createObject([
            'class' => DeleteUserType::class
        ], [
            'delete',
            $this
        ]);

        return $action->runWithParams([
            'type' => $type
        ]);
    }
}
