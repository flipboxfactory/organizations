<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\cp\controllers;

use Craft;
use craft\controllers\ElementIndexesController;
use craft\elements\User;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterElementHtmlAttributesEvent;
use flipbox\organization\elements\actions\RemoveUsers;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserIndexesController extends ElementIndexesController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Event::on(
            User::class,
            User::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions = [
                    [
                        'type' => RemoveUsers::class,
                        'organization' => $event->data['organization'] ?? null
                    ]
                ];
            },
            [
                'organization' => $this->getOrganizationIdFromRequest()
            ]
        );

        // Add 'organizations' on the user html element
        Event::on(
            User::class,
            User::EVENT_REGISTER_HTML_ATTRIBUTES,
            function (RegisterElementHtmlAttributesEvent $event) {
                $event->htmlAttributes['data-organization'] = $event->data['organization'] ?? null;
            },
            [
                'organization' => $this->getOrganizationIdFromRequest()
            ]
        );

        parent::init();
    }

    /**
     * @return mixed
     */
    private function getOrganizationIdFromRequest()
    {
        return Craft::$app->getRequest()->getParam('organization');
    }
}
