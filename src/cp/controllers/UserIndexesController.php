<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers;

use Craft;
use craft\controllers\ElementIndexesController;
use craft\elements\User;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterElementHtmlAttributesEvent;
use flipbox\organizations\elements\actions\DissociateUsersFromOrganizationAction;
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
                        'type' => DissociateUsersFromOrganizationAction::class,
                        'organization' => $event->data['organization'] ?? null
                    ]
                ];
            },
            [
                'organization' => $this->getOrganizationFromRequest()
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
                'organization' => $this->getOrganizationFromRequest()
            ]
        );

        parent::init();
    }

    /**
     * @return mixed
     */
    private function getOrganizationFromRequest()
    {
        return Craft::$app->getRequest()->getParam('organization');
    }
}
