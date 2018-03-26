<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\organization\db\UserCategoryQuery;
use flipbox\organization\Organization;
use yii\web\Response;

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
                    'only' => ['save-associations'],
                    'actions' => [
                        'save-associations' => [201]
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'save-associations' => [
                            201 => Craft::t('organizations', "User Category successfully created."),
                            401 => Craft::t('organizations', "Failed to create User Category.")
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
            'save-associations' => ['post'],
            'get-editor-html' => ['post']
        ];
    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveAssociations(): Response
    {
        $request = Craft::$app->getRequest();

        $user = Craft::$app->getUsers()->getUserById(
            (int)$request->getRequiredBodyParam('user')
        );

        $organization = Organization::getInstance()->getOrganizations()->get(
            $request->getRequiredBodyParam('organization')
        );

        $query = Organization::getInstance()->getUserCategories()->getQuery([
            'id' => array_keys(array_filter((array)$request->getRequiredBodyParam('categories')))
        ]);

        $query->setCachedResult(
            $query->all()
        );

        Organization::getInstance()->getUserCategories()->saveAssociations(
            $query,
            $user,
            $organization
        );

        return $this->asJson(['success' => true]);
    }

    /**
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetEditorHtml(): Response
    {
        $request = Craft::$app->getRequest();

        $user = Craft::$app->getUsers()->getUserById(
            $request->getRequiredBodyParam('user')
        );

        $organization = Organization::getInstance()->getOrganizations()->get(
            $request->getRequiredBodyParam('organization')
        );

        $response = [];

        $view = $this->getView();
        $response['html'] = $view->renderTemplate(
            "organizations/_cp/_components/userCategoriesEditorHtml",
            [
                'user' => $user,
                'organization' => $organization,
                'categories' => (new UserCategoryQuery([
                    'organization' => $organization->id,
                    'user' => $user->id
                ]))->all()
            ]
        );
        $response['headHtml'] = $view->getHeadHtml();
        $response['footHtml'] = $view->getBodyHtml();

        return $this->asJson($response);
    }
}
