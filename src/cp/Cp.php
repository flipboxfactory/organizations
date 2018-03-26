<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp;

use Craft;
use flipbox\organizations\Organizations;
use yii\base\Module as BaseModule;
use yii\web\NotFoundHttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Organizations $module
 */
class Cp extends BaseModule
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Craft::$app->request->getIsCpRequest()) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @return services\Settings
     */
    public function getSettings()
    {
        return $this->get('settings');
    }
}
