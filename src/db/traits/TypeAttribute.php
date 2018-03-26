<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\db\traits;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organization\Organization as OrganizationPlugin;
use flipbox\organization\records\Type;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TypeAttribute
{
    /**
     * The organization type(s) that the results must have.
     *
     * @var string|string[]|int|int[]|Type|Type[]|null $value
     */
    public $type;

    /**
     * @param string|string[]|int|int[]|Type|Type[]|null $value
     * @return self The query object itself
     */
    public function setType($value)
    {
        $this->type = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|Type|Type[]|null $value
     * @return static The query object
     */
    public function type($value)
    {
        return $this->setType($value);
    }

    /**
     * @param string|string[]|int|int[]|Type|Type[]|null $value
     * @return $this
     */
    public function setTypeId($value)
    {
        return $this->setType($value);
    }

    /**
     * @param string|string[]|int|int[]|Type|Type[]|null $value
     * @return self The query object itself
     */
    public function typeId($value)
    {
        return $this->setType($value);
    }

    /**
     * @param $value
     * @param string $join
     * @return array
     */
    protected function parseTypeValue($value, string $join = 'and'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveTypeValue($operator, $v);
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
    protected function resolveTypeValue($operator, &$value)
    {
        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                $value = $this->resolveTypeStringValue($value);
            }

            if ($value instanceof Type) {
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
        if (!$model = OrganizationPlugin::getInstance()->getTypes()->find($value)) {
            return null;
        }
        return $model->id;
    }
}
