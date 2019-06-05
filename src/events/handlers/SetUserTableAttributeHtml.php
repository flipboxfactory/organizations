<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\SetElementTableAttributeHtmlEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SetUserTableAttributeHtml
{
    /**
     * @param SetElementTableAttributeHtmlEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function handle(SetElementTableAttributeHtmlEvent $event)
    {
        if ($event->attribute === 'organizations') {
            $event->html = \Craft::$app->getView()->renderTemplate(
                'organizations/_components/tableAttributes/organizations',
                [
                    'organizations' => $event->sender->getOrganizations()
                ]
            );
        }
    }
}
