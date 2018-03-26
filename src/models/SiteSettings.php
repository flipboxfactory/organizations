<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\models;

use Craft;
use craft\validators\UriFormatValidator;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\models\Model;
use flipbox\ember\traits\SiteAttribute;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SiteSettings extends Model implements SiteSettingsInterface
{
    use SiteAttribute;

    /**
     * @var boolean Has URLs
     */
    public $enabledByDefault = true;

    /**
     * @var boolean Has URLs
     */
    public $hasUrls = false;

    /**
     * @var string URL format
     */
    public $uriFormat;

    /**
     * @var string Template
     */
    public $template;

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            $this->siteAttributes()
        );
    }

    /**
     * @return bool
     */
    public function hasUrls(): bool
    {
        return (bool)$this->hasUrls;
    }

    /**
     * @return string
     */
    public function getUriFormat()
    {
        return $this->uriFormat;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->siteAttributeLabels(),
            [
                'hasUrls' => Craft::t('app', 'Has Urls'),
                'uriFormat' => Craft::t('app', 'URI Format'),
                'template' => Craft::t('app', 'Template'),
                'enabledByDefault' => Craft::t('app', 'Enabled by Default')
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->siteRules(),
            [
                [
                    [
                        'template'
                    ],
                    'string',
                    'max' => 500
                ],
                [
                    [
                        'uriFormat'
                    ],
                    UriFormatValidator::class
                ],
                [
                    [
                        'enabledByDefault',
                        'hasUrls',
                        'uriFormat',
                        'template'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ],
                [
                    [
                        'uriFormat',
                        'template'
                    ],
                    'required',
                    'when' => function ($model) {
                        return $model->hasUrls == true;
                    },
                    'enableClientValidation' => false
                ]
            ]
        );
    }
}
