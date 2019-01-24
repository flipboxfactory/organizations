<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\Query;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationAttributeTrait
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
     * @return array|string
     */
    protected function parseOrganizationValue($value)
    {
        return QueryHelper::prepareParam(
            $value,
            function (string $slug) {
                $value = (new Query())
                    ->select(['id'])
                    ->from(['{{%elements_sites}} elements_sites'])
                    ->where(['slug' => $slug])
                    ->scalar();
                return empty($value) ? false : $value;
            }
        );
    }
}
