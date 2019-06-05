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
use flipbox\organizations\behaviors\OrganizationsAssociatedToUserBehavior;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SetOrganizationUserElementTableAttributeHtml
{
    /**
     * @param SetElementTableAttributeHtmlEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function handle(SetElementTableAttributeHtmlEvent $event)
    {
        if (!in_array($event->attribute, ['state', 'types', 'edit'], true)) {
            return;
        }

        if ($event->attribute === 'edit') {
            $event->html = '<span class="edit-association icon settings"></span>';
            return;
        }

        /** @var User|OrganizationsAssociatedToUserBehavior $element */
        $element = $event->sender;

        $association = $element->getOrganizationManager()->findOne(
            Craft::$app->getRequest()->getParam('organization')
        );

        switch ($event->attribute) {
            case 'state':
                $params = [
                    'indicator' => '',
                    'label' => 'N/A'
                ];

                $state = Organizations::getInstance()->getSettings()->getUserStates();

                if ($association) {
                    $params = [
                        'indicator' => $association->state,
                        'label' => $state[$association->state] ?? 'N/A'
                    ];
                }

                $event->html = Html::encodeParams(
                    '<span class="user-state status {indicator}"></span>{label}',
                    $params
                );
                break;

            case 'types':
                $types = $association ? $association->types : [];

                $html = $label = [];
                foreach ($types as $type) {
                    $label[] = $type->name;
                    $html[] = self::icon($type);
                }

                $event->html = Html::encodeParams(
                    '<span class="user-types-icons" data-label="' . implode(', ', $label) . '">' . implode(
                        '',
                        $html
                    ) . '</span>',
                    []
                );

                break;
        }
    }

    /**
     * @param UserType $type
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private static function icon(UserType $type)
    {
        return '<span class="foo" data-label="' . $type->name . '">' . Craft::$app->getView()->renderTemplate(
            "organizations/_includes/icon.svg",
            [
                'label' => $type->name
            ]
        ) . '</span>';
    }
}
