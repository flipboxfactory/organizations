<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\validators;

use craft\helpers\Json;
use flipbox\organizations\behaviors\UserOrganizationsBehavior;
use flipbox\organizations\Organizations as OrganizationPlugin;
use yii\base\Model;
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
            $this->message = OrganizationPlugin::t(static::DEFAULT_MESSAGE);
        }
    }

    /**
     * Validates a single attribute.
     * Child classes must implement this method to provide the actual validation logic.
     * @param Model|UserOrganizationsBehavior $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$model->getOrganizationManager()->isMutated()) {
            return;
        }

        $result = $this->validateOrganizations($model->getOrganizations());
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * @param array $organizations
     * @return array|null
     */
    private function validateOrganizations(array $organizations)
    {
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
