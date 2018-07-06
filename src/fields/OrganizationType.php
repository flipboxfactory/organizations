<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\OrganizationType as OrganizationTypeRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationType extends Field
{
    /**
     * @var string
     */
    public $defaultSelectionLabel = '-- Select Organization Type --';
    
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('organizations', 'Organization Type');
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'organizations/_components/fieldtypes/OrganizationType/input',
            [
                'field' => $this,
                'value' => $value,
                'element' => $element
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'organizations/_components/fieldtypes/OrganizationType/settings',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Db::getNumericalColumnType();
    }

    /**
     * @inheritdoc
     *
     * @return OrganizationTypeRecord|null
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof OrganizationTypeRecord) {
            return $value;
        }

        if (is_numeric($value)) {
            return Organizations::getInstance()->getOrganizationTypes()->find($value);
        }

        return null;
    }

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return array|int|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof OrganizationTypeRecord) {
            return $value->getId();
        }

        return null;
    }
}
