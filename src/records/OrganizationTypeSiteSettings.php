<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\records;

use Craft;
use craft\queue\jobs\ResaveElements;
use craft\validators\UriFormatValidator;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\SiteAttributeTrait;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\models\SiteSettingsInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property bool $enabledByDefault
 * @property bool $hasUrls
 * @property string $uriFormat
 * @property string $template
 */
class OrganizationTypeSiteSettings extends ActiveRecord implements SiteSettingsInterface
{
    use OrganizationTypeAttributeTrait,
        SiteAttributeTrait;

    /**
     * The table name
     */
    const TABLE_ALIAS = OrganizationType::TABLE_ALIAS . '_sites';

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
                        'enabledByDefault',
                        'hasUrls',
                        'uriFormat',
                        'template'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
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

    /*******************************************
     * PROJECT CONFIG
     *******************************************/

    /**
     * Return an array suitable for Craft's Project config
     */
    public function toProjectConfig(): array
    {
        return $this->toArray([
            'enabledByDefault',
            'hasUrls',
            'uriFormat',
            'template'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert === true) {
            return;
        }

        if (array_key_exists('uriFormat', $changedAttributes) ||
            (array_key_exists('hasUrls', $changedAttributes) &&
                $this->getOldAttribute('hasUrls') != $this->getAttribute('hasUrls'))
        ) {
            $this->reSaveOrganizations();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $this->reSaveOrganizations();
        parent::afterDelete();
    }

    /**
     * Save organizations
     */
    protected function reSaveOrganizations()
    {
        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => Craft::t('organizations', 'Re-saving organizations (Site: {site})', [
                'site' => $this->getSiteId(),
            ]),
            'elementType' => OrganizationElement::class,
            'criteria' => [
                'siteId' => $this->getSiteId(),
                'typeId' => $this->getTypeId(),
                'status' => null,
                'enabledForSite' => false,
            ]
        ]));
    }
}
