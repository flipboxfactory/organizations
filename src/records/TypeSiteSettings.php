<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\records;

use craft\validators\UriFormatValidator;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecord;
use flipbox\ember\records\traits\SiteAttribute;
use flipbox\organization\models\SiteSettingsInterface;
use flipbox\organization\Organization as OrganizationPlugin;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property bool $hasUrls
 * @property string $uriFormat
 * @property string $template
 */
class TypeSiteSettings extends ActiveRecord implements SiteSettingsInterface
{
    use traits\TypeAttribute,
        SiteAttribute;

    /**
     * The table name
     */
    const TABLE_ALIAS = Type::TABLE_ALIAS . '_sites';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['siteId', 'typeId'];

    /**
     * @return bool
     */
    public function hasUrls(): bool
    {
        return (bool)$this->getAttribute('hasUrls');
    }

    /**
     * @return string
     */
    public function getUriFormat()
    {
        return $this->getAttribute('uriFormat');
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->getAttribute('template');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->typeRules(),
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


    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        OrganizationPlugin::getInstance()->getTypeSettings()->afterSave($this, $insert, $changedAttributes);
        parent::afterSave($insert, $changedAttributes);
    }
}
