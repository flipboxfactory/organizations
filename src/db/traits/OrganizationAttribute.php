<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\db\traits;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organization\elements\Organization;
use flipbox\organization\Organizations as OrganizationPlugin;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationAttribute
{
    /**
     * The organization(s) that the resulting types must have.
     *
     * @var string|string[]|int|int[]|Organization|Organization[]|null $value
     */
    public $organization;

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return self The query object itself
     */
    public function setOrganization($value)
    {
        $this->organization = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return static The query object
     */
    public function organization($value)
    {
        return $this->setOrganization($value);
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return $this
     */
    public function setOrganizationId($value)
    {
        return $this->setOrganization($value);
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return self The query object itself
     */
    public function organizationId($value)
    {
        return $this->setOrganization($value);
    }

    /**
     * @param $value
     * @param string $join
     * @return array
     */
    protected function parseOrganizationValue($value, string $join = 'and'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveOrganizationValue($operator, $v);
            }
        }

        $value = ArrayHelper::filterEmptyAndNullValuesFromArray($value);

        if (empty($value)) {
            return [];
        }

        return array_merge([$join], $value);
    }

    /**
     * @param $operator
     * @param $value
     */
    protected function resolveOrganizationValue($operator, &$value)
    {
        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                if ($element = OrganizationPlugin::getInstance()->getOrganizations()->find($value)) {
                    $value = $element->id;
                }
            }

            if ($value instanceof Organization) {
                $value = $value->id;
            }

            if ($value) {
                $value = QueryHelper::assembleParamValue($value, $operator);
            }
        }
    }
}
