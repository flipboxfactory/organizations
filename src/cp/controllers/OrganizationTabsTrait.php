<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember
 */

namespace flipbox\organizations\cp\controllers;

use Craft;
use craft\base\Field;
use craft\models\FieldLayoutTab;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationTabsTrait
{
    /**
     * @param OrganizationElement $organization
     * @param bool $includeUsers
     * @return array
     */
    protected function getTabs(OrganizationElement $organization, bool $includeUsers = true): array
    {
        $tabs = [];

        $count = 1;
        foreach ($organization->getFieldLayout()->getTabs() as $tab) {
            $tabs[] = $this->getTab($organization, $tab, $count++);
        }

        if (null !== $organization->getId() &&
            true === $includeUsers
        ) {
            if (($tab = Organizations::getInstance()->getSettings()->getUsersTabOrder()) > 0) {
                array_splice($tabs, $tab - 1, 0, [[
                    'label' => Organizations::t(Organizations::getInstance()->getSettings()->getUsersTabLabel()),
                    'url' => '#user-index'
                ]]);
            }
        }

        return $tabs;
    }

    /**
     * @param OrganizationElement $organization
     * @param FieldLayoutTab $tab
     * @param int $count
     * @return array
     */
    protected function getTab(OrganizationElement $organization, FieldLayoutTab $tab, int $count): array
    {
        $hasErrors = false;
        if ($organization->hasErrors()) {
            foreach ($tab->getFields() as $field) {
                /** @var Field $field */
                $hasErrors = $organization->getErrors($field->handle) ? true : $hasErrors;
            }
        }

        return [
            'label' => $tab->name,
            'url' => '#tab' . $count,
            'class' => $hasErrors ? 'error' : null
        ];
    }
}
