<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\traits;

use Craft;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TypeAttribute
{
    use TypeRules, TypeMutator;

    /**
     * @var int|null
     */
    private $typeId;

    /**
     * @return array
     */
    protected function typeFields(): array
    {
        return [
            'typeId'
        ];
    }

    /**
     * @return array
     */
    protected function typeAttributes(): array
    {
        return [
            'typeId'
        ];
    }

    /**
     * @return array
     */
    protected function typeAttributeLabels(): array
    {
        return [
            'typeId' => Craft::t('organizations', 'Type Id')
        ];
    }
}
