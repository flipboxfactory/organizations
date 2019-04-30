<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\db\Query;
use craft\db\QueryAbortedException;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\records\OrganizationType;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait OrganizationTypeAttributeTrait
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
     * @return array|string
     * @throws QueryAbortedException
     */
    protected function parseOrganizationTypeValue($value)
    {
        $return = QueryHelper::prepareParam(
            $value,
            function (string $handle) {
                $value = (new Query())
                    ->select(['id'])
                    ->from([OrganizationType::tableName()])
                    ->where(['handle' => $handle])
                    ->scalar();
                return empty($value) ? false : $value;
            }
        );

        if ($return !== null && empty($return)) {
            throw new QueryAbortedException();
        }

        return $return;
    }
}
