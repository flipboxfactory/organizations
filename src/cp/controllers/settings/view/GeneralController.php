<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\settings\view;

use Craft;
use craft\helpers\UrlHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class GeneralController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractController::TEMPLATE_BASE . '/general';

    /**
     * OrganizationIndex
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['fullPageForm'] = true;
        $variables['tabs'] = $this->getTabs();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /*******************************************
     * TABS
     *******************************************/

    /**
     * @return array|null
     */
    protected function getTabs(): array
    {
        return [
            'general' => [
                'label' => Craft::t('organizations', 'General'),
                'url' => '#general'
            ],
            'layout' => [
                'label' => Craft::t('organizations', 'Layout'),
                'url' => '#layout'
            ]
        ];
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/general';
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);

        $variables['crumbs'][] = [
            'label' => Craft::t('organizations', 'General'),
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
