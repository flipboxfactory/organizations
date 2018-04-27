<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\settings\view;

use Craft;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\organizations\records\UserType;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserTypesController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractController::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'types';

    /**
     * The insert/update view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * @return \flipbox\organizations\services\UserTypes
     */
    protected function userTypeService()
    {
        return $this->module->module->getUserTypes();
    }

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['types'] = $this->userTypeService()->findAll();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * Insert/Update
     *
     * @param string|int|null $identifier
     * @param UserType $userType
     * @return Response
     */
    public function actionUpsert($identifier = null, UserType $userType = null)
    {
        if (null === $userType) {
            if (null === $identifier) {
                $userType = $this->userTypeService()->create();
            } else {
                $userType = $this->userTypeService()->get($identifier);
            }
        }

        $variables = [];
        if ($userType->getIsNewRecord()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $userType);
        }

        $variables['type'] = $userType;
        $variables['fullPageForm'] = true;

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/user-types';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/user-types';
    }

    /*******************************************
     * INSERT VARIABLES
     *******************************************/

    /**
     * @param array $variables
     */
    protected function insertVariables(array &$variables)
    {
        parent::insertVariables($variables);
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/{id}');
    }

    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param UserType $userType
     */
    protected function updateVariables(array &$variables, UserType $userType)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Craft::t('organizations', 'Edit') . ' ' . $userType->name;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $userType->getId());
        $variables['crumbs'][] = [
            'label' => Craft::t('organizations', $userType->name),
            'url' => UrlHelper::url($variables['continueEditingUrl'])
        ];
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        $title = Craft::t('organizations', 'User Types');

        parent::baseVariables($variables);
        $variables['title'] .= ': ' . $title;
        $variables['crumbs'][] = [
            'label' => $title,
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
