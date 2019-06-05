<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Edit as EditAction;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\craft\ember\elements\ExplicitElementTrait;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\organizations\models\DateJoinedAttributeTrait;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\records\Organization as OrganizationRecord;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationType as TypeModel;
use yii\base\ErrorException as Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method static Organization[] findAll($criteria = null) : array
 */
class Organization extends Element
{
    use ExplicitElementTrait,
        DateJoinedAttributeTrait,
        TypesAttributeTrait,
        UsersAttributeTrait;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('organizations', 'Organization');
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * Returns whether this element type can have statuses.
     *
     * @return boolean
     */
    public static function hasStatuses(): bool
    {
        return true;
    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @inheritdoc
     *
     * @return OrganizationQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new OrganizationQuery(static::class);
    }

    /**
     * @inheritdoc
     * @return static|null
     */
    public static function findOne($criteria = null)
    {
        if ($criteria instanceof self) {
            return $criteria;
        }

        // If we're asking by PK, ignore status
        if (is_numeric($criteria)) {
            $criteria = [
                'id' => $criteria,
                'status' => null
            ];
        }

        return parent::findOne($criteria);
    }

    /**
     * @param mixed $criteria
     * @param bool $one
     * @return Element|Element[]|null
     */
    protected static function findByCondition($criteria, bool $one)
    {
        if (is_numeric($criteria)) {
            $criteria = ['id' => $criteria];
        }

        if (is_string($criteria)) {
            $criteria = ['slug' => $criteria];
        }

        return parent::findByCondition($criteria, $one);
    }


    /************************************************************
     * PROPERTIES / ATTRIBUTES
     ************************************************************/

    /**
     * @return array
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            $this->dateJoinedAttributes()
        );
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->dateJoinedRules(),
            [
                [
                    [
                        'types',
                        'activeType',
                        'primaryType',
                        'users',
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]

                ],
            ]
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->dateJoinedAttributeLabels()
        );
    }


    /************************************************************
     * FIELD LAYOUT
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        if (null === ($type = $this->getActiveType())) {
            return OrganizationPlugin::getInstance()->getSettings()->getFieldLayout();
        }

        return $type->getFieldLayout();
    }


    /************************************************************
     * ELEMENT ADMIN
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function getRef()
    {
        if (!$primary = $this->getPrimaryType()) {
            return $this->slug;
        }

        return $primary->handle . '/' . $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('organizations/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        switch ($context) {
            case 'user':
                return self::defineUserSources();

            default:
                return self::defineTypeSources();
        }
    }

    /**
     * @return array
     */
    private static function defineDefaultSources(): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('organizations', 'All organizations'),
                'criteria' => ['status' => null],
                'hasThumbs' => true
            ]
        ];
    }

    /**
     * @return array
     */
    private static function defineTypeSources(): array
    {
        $sources = self::defineDefaultSources();

        // Array of all organization types
        $organizationTypes = OrganizationType::findAll([]);

        $sources[] = ['heading' => Craft::t('organizations', 'Types')];

        /** @var TypeModel $organizationType */
        foreach ($organizationTypes as $organizationType) {
            $sources[] = [
                'key' => 'type:' . $organizationType->id,
                'label' => $organizationType->name,
                'criteria' => ['status' => null, 'typeId' => $organizationType->id],
                'hasThumbs' => true
            ];
        }

        return $sources;
    }

    /**
     * @return array
     */
    private static function defineUserSources(): array
    {
        $sources = self::defineDefaultSources();

        // Array of all organization types
        $organizationUsers = User::find();

        $sources[] = ['heading' => Craft::t('organizations', 'Users')];

        /** @var User $organizationUser */
        foreach ($organizationUsers as $organizationUser) {
            $sources[] = [
                'key' => 'user:' . $organizationUser->id,
                'label' => $organizationUser->getFullName(),
                'criteria' => [
                    'status' => null,
                    'users' => [$organizationUser->id]
                ],
                'hasThumbs' => true
            ];
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Status
        $actions[] = SetStatus::class;

        // Edit
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => EditAction::class,
            'label' => Craft::t('organizations', 'Edit'),
        ]);

//        if (Craft::$app->getUser()->checkPermission('deleteOrganizations')) {
//            // Delete Organization
//            $actions[] = DeleteAction::class;
//        }

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return [
            'id'
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('organizations', 'Name'),
            'dateJoined' => Craft::t('organizations', 'Join Date'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function eagerLoadingMap(array $sourceElements, string $handle)
    {
        switch ($handle) {
            case 'users':
                return self::eagerLoadingUsersMap($sourceElements);
        }

        return parent::eagerLoadingMap($sourceElements, $handle);
    }

    /**
     * @inheritdoc
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        switch ($handle) {
            case 'users':
                $users = $elements ?? [];
                $this->getUserManager()->setMany($users);
                break;

            default:
                parent::setEagerLoadedElements($handle, $elements);
        }
    }

    /**
     * @inheritdoc
     */
    public static function defineTableAttributes(): array
    {
        return [
            'id' => ['label' => Craft::t('app', 'Name')],
            'uri' => ['label' => Craft::t('app', 'URI')],
            'types' => ['label' => Craft::t('organizations', 'Type(s)')],
            'dateJoined' => ['label' => Craft::t('organizations', 'Join Date')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }



    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function tableAttributeHtml(string $attribute): string
    {

        switch ($attribute) {
            case 'types':
                $typeHtmlParts = [];
                foreach ($this->getTypes() as $type) {
                    $typeHtmlParts[] = '<a href="' .
                        UrlHelper::cpUrl('organizations/' . $this->id . '/' . $type->handle) .
                        '">' .
                        $type->name .
                        '</a>';
                }

                return !empty($typeHtmlParts) ? StringHelper::toString($typeHtmlParts, ', ') : '';
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    public function getUriFormat()
    {
        if (null === ($siteSettings = $this->getSiteSettings())) {
            return null;
        }

        if (!$siteSettings->hasUrls()) {
            return null;
        }

        return $siteSettings->getUriFormat();
    }

    /**
     * @inheritdoc
     */
    public function route()
    {
        if (in_array(
            $this->getStatus(),
            [static::STATUS_DISABLED, static::STATUS_ARCHIVED],
            true
        )) {
            return null;
        }

        if (null === ($siteSettings = $this->getSiteSettings())) {
            return null;
        }

        if (!$siteSettings->hasUrls()) {
            return null;
        }

        return [
            'templates/render',
            [
                'template' => $siteSettings->getTemplate(),
                'variables' => [
                    'organization' => $this,
                ]
            ]
        ];
    }

    /**
     * @return \flipbox\organizations\records\OrganizationTypeSiteSettings|null
     */
    protected function getSiteSettings()
    {
        try {
            $settings = OrganizationPlugin::getInstance()->getSettings();
            $siteSettings = $settings->getSiteSettings()[$this->siteId] ?? null;

            if (null !== ($type = $this->getPrimaryType())) {
                $siteSettings = $type->getSiteSettings()[$this->siteId] ?? $siteSettings;
            }

            return $siteSettings;
        } catch (\Exception $e) {
            OrganizationPlugin::error(
                sprintf(
                    "An exception was caught while to resolve site settings: %s",
                    $e->getMessage()
                )
            );
        }

        return null;
    }

    /************************************************************
     * EVENTS
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        if (empty($this->getDateJoined())) {
            $this->setDateJoined(DateTimeHelper::currentUTCDateTime());
        }

        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     * @throws /Exception
     */
    public function afterSave(bool $isNew)
    {
        if (false === $this->saveRecord($isNew)) {
            throw new Exception('Unable to save organization record');
        }

        // Types
        if (false === $this->getTypeManager()->save()) {
            throw new Exception("Unable to save types.");
        }

        // Users
        if (false === $this->getUserManager()->save()) {
            throw new Exception("Unable to save users.");
        }

        parent::afterSave($isNew);
    }

    /*******************************************
     * RECORD
     *******************************************/

    /**
     * @param bool $isNew
     * @return bool
     * @throws \Exception
     */
    protected function saveRecord(bool $isNew): bool
    {
        $record = $this->elementToRecord();

        if (!$record->save()) {
            $this->addErrors($record->getErrors());

            OrganizationPlugin::error(
                Json::encode($this->getErrors()),
                __METHOD__
            );

            return false;
        }

        if (false !== ($dateUpdated = DateTimeHelper::toDateTime($record->dateUpdated))) {
            $this->dateUpdated = $dateUpdated;
        }


        if ($isNew) {
            $this->id = $record->id;

            if (false !== ($dateCreated = DateTimeHelper::toDateTime($record->dateCreated))) {
                $this->dateCreated = $dateCreated;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     * @return OrganizationRecord
     */
    protected function elementToRecord(): OrganizationRecord
    {
        if (!$record = OrganizationRecord::findOne([
            'id' => $this->getId()
        ])) {
            $record = new OrganizationRecord();
        }

        // PopulateOrganizationTypeTrait the record attributes
        $record->id = $this->getId();
        $record->dateJoined = $this->getDateJoined();

        return $record;
    }
}
