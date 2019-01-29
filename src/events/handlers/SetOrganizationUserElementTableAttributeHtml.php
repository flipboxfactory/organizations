<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use Craft;
use craft\elements\User;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\helpers\Html;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SetOrganizationUserElementTableAttributeHtml
{
    /**
     * @param SetElementTableAttributeHtmlEvent $event
     */
    public static function handle(SetElementTableAttributeHtmlEvent $event)
    {
        if ($event->attribute === 'state') {
            /** @var User $element */
            $element = $event->sender;

            $params = [
                'indicator' => '',
                'label' => 'N/A'
            ];

            $organizaiton = Craft::$app->getRequest()->getParam('organization');
            $state = Organizations::getInstance()->getSettings()->getUserStates();

            if ($association = UserAssociation::findOne([
                'user' => $element,
                'organization' => $organizaiton
            ])) {
                $params = [
                    'indicator' => $association->state,
                    'label' => $state[$association->state] ?? 'N/A'
                ];
            }

            $event->html = Html::encodeParams(
                '<span class="user-state status {indicator}"></span>{label}',
                $params
            );
        }
    }
}
