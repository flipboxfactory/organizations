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
use flipbox\organizations\actions\users\AssociateUserToOrganization;
use flipbox\organizations\actions\users\DissociateUserFromOrganization;
use flipbox\organizations\behaviors\UserTypesAssociatedToUserBehavior;
use flipbox\organizations\events\handlers\RegisterOrganizationUserElementDefaultTableAttributes;
use flipbox\organizations\events\handlers\RegisterOrganizationUserElementTableAttributes;
use flipbox\organizations\events\handlers\SetOrganizationUserElementTableAttributeHtml;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use yii\base\Event;

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

    /**
     * @param null $user
     * @param null $organization
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionSaveAssociation($user = null, $organization = null)
    {
        if (null === $organization) {
            $organization = Craft::$app->getRequest()->getBodyParam('organization');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam(
                'user',
                Craft::$app->getRequest()->getBodyParam('elementId')
            );
        }

        $userAssociation = UserAssociation::findOne([
            'user' => $user,
            'organization' => $organization
        ]);

        $success = true;

        $userAssociation->state = Craft::$app->getRequest()->getBodyParam('state', $userAssociation->state);

        $userAssociation->getTypes()->clear()->add(
            Craft::$app->getRequest()->getBodyParam('types')
        );

        if (!$userAssociation->save()) {
            $success = false;
        }

        $user = $userAssociation->getUser();

        $response = [
            'success' => $success,
            'id' => $user->getId(),
            'newTitle' => (string)$user,
            'cpEditUrl' => $user->getCpEditUrl(),
        ];

        // Should we be including table attributes too?
        $sourceKey = Craft::$app->getRequest()->getBodyParam('includeTableAttributesForSource');

        if ($sourceKey) {
            Event::on(
                User::class,
                User::EVENT_REGISTER_DEFAULT_TABLE_ATTRIBUTES,
                [
                    RegisterOrganizationUserElementDefaultTableAttributes::class,
                    'handle'
                ]
            );

            // Add attributes the user index
            Event::on(
                User::class,
                User::EVENT_REGISTER_TABLE_ATTRIBUTES,
                [
                    RegisterOrganizationUserElementTableAttributes::class,
                    'handle'
                ]
            );

            // Add 'organizations' on the user html element
            Event::on(
                User::class,
                User::EVENT_SET_TABLE_ATTRIBUTE_HTML,
                [
                    SetOrganizationUserElementTableAttributeHtml::class,
                    'handle'
                ]
            );

            $attributes = Craft::$app->getElementIndexes()->getTableAttributes(get_class($user), $sourceKey);

            // Drop the first one
            array_shift($attributes);

            foreach ($attributes as $attribute) {
                $response['tableAttributes'][$attribute[0]] = $user->getTableAttributeHtml($attribute[0]);
            }

            Event::off(
                User::class,
                User::EVENT_REGISTER_DEFAULT_TABLE_ATTRIBUTES,
                [
                    RegisterOrganizationUserElementDefaultTableAttributes::class,
                    'handle'
                ]
            );

            // Add attributes the user index
            Event::off(
                User::class,
                User::EVENT_REGISTER_TABLE_ATTRIBUTES,
                [
                    RegisterOrganizationUserElementTableAttributes::class,
                    'handle'
                ]
            );

            // Add 'organizations' on the user html element
            Event::off(
                User::class,
                User::EVENT_SET_TABLE_ATTRIBUTE_HTML,
                [
                    SetOrganizationUserElementTableAttributeHtml::class,
                    'handle'
                ]
            );
        }

        return $this->asJson($response);
    }

    /**
     * @param null $user
     * @param null $organization
     * @return \yii\web\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function actionAssociationEditorHtml($user = null, $organization = null)
    {
        if (null === $organization) {
            $organization = Craft::$app->getRequest()->getBodyParam('organization');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam(
                'user',
                Craft::$app->getRequest()->getBodyParam('elementId')
            );
        }

        $userAssociation = UserAssociation::findOne([
            'user' => $user,
            'organization' => $organization
        ]);

        $view = Craft::$app->getView();
        return $this->asJson([
            'html' => Craft::$app->getView()->renderTemplate(
                "organizations/_cp/_components/userAssociationEditorHtml",
                [
                    'association' => $userAssociation
                ]
            ),
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml()
        ]);
    }
}
