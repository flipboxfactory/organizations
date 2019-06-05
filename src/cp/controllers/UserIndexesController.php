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
use craft\events\RegisterElementHtmlAttributesEvent;
use flipbox\organizations\events\handlers\RegisterOrganizationUserElementActions;
use flipbox\organizations\events\handlers\RegisterOrganizationUserElementDefaultTableAttributes;
use flipbox\organizations\events\handlers\RegisterOrganizationUserElementTableAttributes;
use flipbox\organizations\events\handlers\SetOrganizationUserElementTableAttributeHtml;
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
            [
                RegisterOrganizationUserElementActions::class,
                'handle'
            ]
        );

        // Add attributes the user index
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
     * @inheritDoc
     */
    protected function includeActions(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    private function getOrganizationFromRequest()
    {
        return Craft::$app->getRequest()->getParam('organization');
    }
}
