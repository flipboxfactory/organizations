<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\queries;

use craft\helpers\Db;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\UserAttributeTrait;
use flipbox\organizations\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserAssociation one($db = null)
 * @method UserAssociation[] all($db = null)
 * @method UserAssociation[] getCachedResult($db = null)
 */
class UserAssociationQuery extends CacheableActiveQuery
{
    use UserAttributeTrait,
        OrganizationAttributeTrait;

    /**
     * @var string|string[]|null
     */
    public $state;

    /**
     * @param string|string[]|null $value
     * @return static The query object
     */
    public function setState($value)
    {
        $this->state = $value;
        return $this;
    }

    /**
     * @param string|string[]|null $value
     * @return static The query object
     */
    public function state($value)
    {
        return $this->setState($value);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->from([
            UserAssociation::tableName() . ' ' . UserAssociation::tableAlias()
        ]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function prepare($builder)
    {
        $attributes = ['state'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->{$attribute})) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }

        if ($this->user !== null) {
            $this->andWhere(
                Db::parseParam('userId', $this->parseUserValue($this->user))
            );
        }

        if ($this->organization !== null) {
            $this->andWhere(
                Db::parseParam('organizationId', $this->parseOrganizationValue($this->organization))
            );
        }

        return parent::prepare($builder);
    }
}
