<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\controllers\settings;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\organization\actions\users\categories\Create;
use flipbox\organization\actions\users\categories\Delete;
use flipbox\organization\actions\users\categories\Update;
use flipbox\organization\cp\controllers\AbstractController;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserCategoriesController extends AbstractController
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
                    'default' => 'category'
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
                            201 => Craft::t('organizations', "User Category successfully created."),
                            401 => Craft::t('organizations', "Failed to create User Category.")
                        ],
                        'update' => [
                            200 => Craft::t('organizations', "User Category successfully updated."),
                            401 => Craft::t('organizations', "Failed to update User Category.")
                        ],
                        'delete' => [
                            204 => Craft::t('organizations', "User Category successfully deleted."),
                            401 => Craft::t('organizations', "Failed to delete User Category.")
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
        /** @var Create $action */
        $action = Craft::createObject([
            'class' => Create::class,
            'checkAccess' => [$this, 'checkCreateAccess']
        ], [
            'create',
            $this
        ]);

        $response = $action->runWithParams([]);

        return $response;
    }

    /**
     * @param string|int|null $category
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($category = null)
    {
        if (null === $category) {
            $category = Craft::$app->getRequest()->getBodyParam('category');
        }

        /** @var Update $action */
        $action = Craft::createObject([
            'class' => Update::class,
            'checkAccess' => [$this, 'checkUpdateAccess']
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([
            'category' => $category
        ]);
    }

    /**
     * @param string|int|null $category
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelete($category = null)
    {
        if (null === $category) {
            $category = Craft::$app->getRequest()->getBodyParam('category');
        }

        /** @var Delete $action */
        $action = Craft::createObject([
            'class' => Delete::class,
            'checkAccess' => [$this, 'checkDeleteAccess']
        ], [
            'delete',
            $this
        ]);

        return $action->runWithParams([
            'category' => $category
        ]);
    }

    /**
     * @return bool
     */
    public function checkCreateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    public function checkUpdateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    public function checkDeleteAccess(): bool
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
