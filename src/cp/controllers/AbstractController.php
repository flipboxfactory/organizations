<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers;

use craft\helpers\ArrayHelper;
use flipbox\craft\ember\filters\FlashMessageFilter;
use flipbox\craft\ember\filters\ModelErrorFilter;
use flipbox\craft\ember\filters\RedirectFilter;
use flipbox\organizations\cp\Cp;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Cp $module
 */
abstract class AbstractController extends \flipbox\craft\ember\controllers\AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'redirect' => [
                    'class' => RedirectFilter::class
                ],
                'error' => [
                    'class' => ModelErrorFilter::class
                ],
                'flash' => [
                    'class' => FlashMessageFilter::class
                ]
            ]
        );
    }
}
