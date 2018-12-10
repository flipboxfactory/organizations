<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\db\traits;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationTypeAttribute
{
    /**
     * The organization type(s) that the results must have.
     *
     * @var string|string[]|int|int[]|OrganizationType|OrganizationType[]|null $value
     */
    public $organizationType;

    /**
     * @param string|string[]|int|int[]|OrganizationType|OrganizationType[]|null $value
     * @return self The query object itself
     */
    public function setOrganizationType($value)
    {
        $this->organizationType = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|OrganizationType|OrganizationType[]|null $value
     * @return static The query object
     */
    public function organizationType($value)
    {
        return $this->setOrganizationType($value);
    }

    /**
     * @param string|string[]|int|int[]|OrganizationType|OrganizationType[]|null $value
     * @return $this
     */
    public function setTypeId($value)
    {
        return $this->setOrganizationType($value);
    }

    /**
     * @param string|string[]|int|int[]|OrganizationType|OrganizationType[]|null $value
     * @return self The query object itself
     */
    public function organizationTypeId($value)
    {
        return $this->setOrganizationType($value);
    }

    /**
     * @param $value
     * @param string $join
     * @return array
     */
    protected function parseOrganizationTypeValue($value, string $join = 'and'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveOrganizationTypeValue($operator, $v);
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
    protected function resolveOrganizationTypeValue($operator, &$value)
    {
        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                $value = $this->resolveTypeStringValue($value);
            }

            if ($value instanceof OrganizationType) {
                $value = $value->id;
            }

            if ($value) {
                $value = QueryHelper::assembleParamValue($value, $operator);
            }
        }
    }

    /**
     * @param string $value
     * @return int|null
     */
    protected function resolveTypeStringValue(string $value)
    {
        if (!$record = OrganizationType::findOne($value)) {
            return null;
        }
        return $record->id;
    }
}
