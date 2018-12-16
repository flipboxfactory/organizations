<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers;

use Craft;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use flipbox\organizations\queries\UserTypeQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use yii\db\Query;
use yii\web\Response;

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
                    'default' => 'type'
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
                            201 => Craft::t('organizations', "User Type successfully created."),
                            401 => Craft::t('organizations', "Failed to create User Type.")
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

        $identifier = $request->getRequiredBodyParam('organization');
        $organization = Organization::getOne($identifier);

        $types = array_keys(array_filter((array)$request->getRequiredBodyParam('types')));
        $query = UserType::find()->id(empty($types) ? ':empty:' : $types);

        $query->setCachedResult(
            $query->all()
        );

        $this->saveAssociations(
            $query,
            $user,
            $organization
        );

        return $this->asJson(['success' => true]);
    }

    /**
     * @param UserTypeQuery $query
     * @param User $user
     * @param Organization $organization
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        UserTypeQuery $query,
        User $user,
        Organization $organization
    ): bool
    {
        return $user->saveUserTypeAssociations($query, $organization);
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

        $identifier = $request->getRequiredBodyParam('organization');

        $organization = Organization::getOne($identifier);

        $response = [];

        $view = $this->getView();
        $response['html'] = $view->renderTemplate(
            "organizations/_cp/_components/userTypesEditorHtml",
            [
                'user' => $user,
                'organization' => $organization,
                'typeOptions' => $this->getUserTypes()
            ]
        );
        $response['headHtml'] = $view->getHeadHtml();
        $response['footHtml'] = $view->getBodyHtml();

        return $this->asJson($response);
    }

    /**
     * @return array
     */
    private function getUserTypes(): array
    {
        $types = UserType::find()
            ->select(['name'])
            ->indexBy('id')
            ->column();

        $lastHeading = null;
        $items = [];
        foreach (Craft::$app->getElementIndexes()->getSources(User::class) as $source) {
            if (array_key_exists('heading', $source)) {
                $lastHeading = $source['heading'];
                continue;
            }

            $label = $source['label'] ?? null;

            if ($label !== null && in_array($label, $types, true)) {
                $items[$lastHeading][array_search($label, $types)] = $label;
            }
        }

        return $items;
    }
}
