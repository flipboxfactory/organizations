<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\models;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
interface SiteSettingsInterface
{
    /**
     * @return int|null
     */
    public function getSiteId();

    /**
     * @return bool
     */
    public function hasUrls(): bool;

    /**
     * @return string|null
     */
    public function getUriFormat();

    /**
     * @return string|null
     */
    public function getTemplate();
}
