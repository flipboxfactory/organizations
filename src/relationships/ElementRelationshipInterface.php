<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\relationships;

use craft\elements\db\ElementQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
interface ElementRelationshipInterface extends RelationshipInterface
{
    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @return ElementQueryInterface
     */
    public function getQuery(): ElementQueryInterface;
}
