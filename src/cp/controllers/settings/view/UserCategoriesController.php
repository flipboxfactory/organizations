<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\controllers\settings\view;

use Craft;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\organization\records\UserCategory;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserCategoriesController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractController::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'categories';

    /**
     * The insert/update view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * @return \flipbox\organization\services\UserCategories
     */
    protected function userCategoryService()
    {
        return $this->module->module->getUserCategories();
    }

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['categories'] = $this->userCategoryService()->findAll();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * Insert/Update
     *
     * @param string|int|null $identifier
     * @param UserCategory $userCategory
     * @return Response
     */
    public function actionUpsert($identifier = null, UserCategory $userCategory = null)
    {
        if (null === $userCategory) {
            if (null === $identifier) {
                $userCategory = $this->userCategoryService()->create();
            } else {
                $userCategory = $this->userCategoryService()->get($identifier);
            }
        }

        $variables = [];
        if ($userCategory->getIsNewRecord()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $userCategory);
        }

        $variables['category'] = $userCategory;
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
        return parent::getBaseActionPath() . '/user-categories';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/user-categories';
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
     * @param UserCategory $userCategory
     */
    protected function updateVariables(array &$variables, UserCategory $userCategory)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Craft::t('organizations', 'Edit') . ' ' . $userCategory->name;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $userCategory->getId());
        $variables['crumbs'][] = [
            'label' => Craft::t('organizations', $userCategory->name),
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
        $title = Craft::t('organizations', 'User Categories');

        parent::baseVariables($variables);
        $variables['title'] .= ': ' . $title;
        $variables['crumbs'][] = [
            'label' => $title,
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
