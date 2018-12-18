<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\events\SetElementTableAttributeHtmlEvent;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use yii\base\ModelEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SetUserTableAttributeHtml
{
    /**
     * @param SetElementTableAttributeHtmlEvent $event
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public static function handle(SetElementTableAttributeHtmlEvent $event)
    {
        if ($event->attribute === 'organizations') {
            $event->html = \Craft::$app->getView()->renderTemplate(
                'organizations/_components/tableAttributes/organizations',
                [
                    'organizations' => $event->sender->getOrganizations(['status' => null])->all()
                ]
            );
        }
    }
}