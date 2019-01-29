<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\web\assets\organization;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use flipbox\craft\elements\nestedIndex\web\assets\index\NestedElementIndex;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Organization extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->js = [
            'js/UserAssociationEditor' . $this->dotJs(),
            'js/OrganizationTypeSwitcher' . $this->dotJs(),
//            'js/OrganizationDelete' . $this->dotJs()
        ];

        $this->css = [
            'css/OrganizationTypeSwitcher.css',
            'css/Organization.css'
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
        NestedElementIndex::class
    ];
}
