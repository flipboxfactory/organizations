<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\validators;

use Craft;
use craft\helpers\Json;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationQuery;
use yii\base\Exception;
use yii\validators\Validator;

/**
 * Validates all associated
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationsValidator extends Validator
{
    const DEFAULT_MESSAGE = 'Invalid organizations.';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Craft::t('organizations', static::DEFAULT_MESSAGE);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$value instanceof OrganizationQuery) {
            throw new Exception("Validation value must be an 'OrganizationQuery'.");
        }

        return $this->validateOrganizationQuery($value);
    }

    /**
     * @inheritdoc
     * @param OrganizationQuery $query
     */
    private function validateOrganizationQuery(OrganizationQuery $query)
    {
        if (null === ($organizations = $query->getCachedResult())) {
            return null;
        }

        $hasError = false;

        foreach ($organizations as $organization) {
            if (null === $organization->id && !$organization->validate()) {
                $hasError = true;

                OrganizationPlugin::warning(
                    sprintf(
                        "Invalid organization: '%s'",
                        Json::encode($organization->getFirstErrors())
                    ),
                    __METHOD__
                );
            }
        }

        if ($hasError) {
            return [$this->message, []];
        }

        return null;
    }
}
