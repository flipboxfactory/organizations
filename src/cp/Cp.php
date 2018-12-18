<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use flipbox\organizations\Organizations;
use yii\base\Event;
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
    public function init()
    {
        parent::init();

        // Base template directory
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['nested-element-index'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-elements-nested-index/src/templates';
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Craft::$app->request->getIsCpRequest()) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }
}
