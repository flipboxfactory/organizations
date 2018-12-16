<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\settings\view;

use Craft;
use flipbox\craft\ember\helpers\UrlHelper;
use flipbox\organizations\cp\controllers\view\AbstractController as BaseAbstractController;
use flipbox\organizations\cp\Cp as CpModule;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property CpModule $module
 */
abstract class AbstractController extends BaseAbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_BASE = parent::TEMPLATE_BASE . '/settings';

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/settings';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/settings';
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

        // Select our sub-nav
        if (!$activeSubNav = Craft::$app->getRequest()->getSegment(3)) {
            $activeSubNav = 'general';
        }
        $variables['selectedSubnavItem'] = 'organizations.' . $activeSubNav;

        $title = Craft::t('organizations', "Settings");
        $variables['title'] = Craft::t('organizations', "Organization") . ' ' . $title;

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => $title,
            'url' => UrlHelper::url($this->getBaseCpPath())
        ];
    }
}
