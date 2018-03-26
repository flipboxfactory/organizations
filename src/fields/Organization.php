<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\fields;

use Craft;
use craft\fields\BaseRelationField;
use flipbox\organizations\elements\Organization as OrganizationElement;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Organization extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('organizations', 'Organizations');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return OrganizationElement::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('organizations', 'Add an organization');
    }
}
