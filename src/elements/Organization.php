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
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\errors\ElementNotFoundException;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\db\OrganizationTypeQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\Organization as OrganizationRecord;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationType as TypeModel;
use flipbox\organizations\records\OrganizationTypeAssociation;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\traits\DateJoinedAttribute;
use yii\base\ErrorException as Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Organization extends Element
{
    use DateJoinedAttribute,
        traits\TypesAttribute,
        traits\UsersAttribute;

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
     * @inheritdoc
     *
     * @return OrganizationQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new OrganizationQuery(static::class);
    }

    /*******************************************
     * GET
     *******************************************/

    /**
     * Returns a single element instance by a primary key or a set of element criteria parameters.
     *
     * The method accepts:
     *
     *  - an int: query by a single ID value and return the corresponding element (or null if not found).
     *  - an array of name-value pairs: query by a set of parameter values and return the first element
     *    matching all of them (or null if not found).
     *
     * Note that this method will automatically call the `one()` method and return an
     * [[ElementInterface|\craft\base\Element]] instance. For example,
     *
     * ```php
     * // find a single entry whose ID is 10
     * $entry = Entry::findOne(10);
     * // the above code is equivalent to:
     * $entry = Entry::find->id(10)->one();
     * // find the first user whose email ends in "example.com"
     * $user = User::findOne(['email' => '*example.com']);
     * // the above code is equivalent to:
     * $user = User::find()->email('*example.com')->one();
     * ```
     *
     * @param mixed $criteria The element ID or a set of element criteria parameters
     * @return static Element instance matching the condition, or null if nothing matches.
     * @throws ElementNotFoundException
     */
    public static function getOne($criteria)
    {
        if (null === ($element = static::findOne($criteria))) {
            throw new ElementNotFoundException(
                sprintf(
                    "Organization not found with the following criteria: %s",
                    Json::encode($criteria)
                )
            );
        }

        return $element;
    }

    /**
     * Returns a list of elements that match the specified ID(s) or a set of element criteria parameters.
     *
     * The method accepts:
     *
     *  - an int: query by a single ID value and return an array containing the corresponding element
     *    (or an empty array if not found).
     *  - an array of integers: query by a list of ID values and return the corresponding elements (or an
     *    empty array if none was found).
     *    Note that an empty array will result in an empty result as it will be interpreted as a search for
     *    primary keys and not an empty set of element criteria parameters.
     *  - an array of name-value pairs: query by a set of parameter values and return an array of elements
     *    matching all of them (or an empty array if none was found).
     *
     * Note that this method will automatically call the `all()` method and return an array of
     * [[ElementInterface|\craft\base\Element]] instances. For example,
     *
     * ```php
     * // find the entries whose ID is 10
     * $entries = Entry::findAll(10);
     * // the above code is equivalent to:
     * $entries = Entry::find()->id(10)->all();
     * // find the entries whose ID is 10, 11 or 12.
     * $entries = Entry::findAll([10, 11, 12]);
     * // the above code is equivalent to:
     * $entries = Entry::find()->id([10, 11, 12]])->all();
     * // find users whose email ends in "example.com"
     * $users = User::findAll(['email' => '*example.com']);
     * // the above code is equivalent to:
     * $users = User::find()->email('*example.com')->all();
     * ```
     *
     * @param mixed $criteria The element ID, an array of IDs, or a set of element criteria parameters
     * @return static[] an array of Element instances, or an empty array if nothing matches.
     * @throws ElementNotFoundException
     */
    public static function getAll($criteria)
    {
        $elements = static::findAll($criteria);

        if (empty($elements)) {
            throw new ElementNotFoundException(
                sprintf(
                    "Organization not found with the following criteria: %s",
                    Json::encode($criteria)
                )
            );
        }

        return $elements;
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

    /**
     * Returns whether this element type can have statuses.
     *
     * @return boolean
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

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
     * @return array
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            $this->dateJoinedAttributes(),
            [
                //                'types',
                //                'activeType',
                //                'primaryType',
                //                'users',
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
            $this->dateJoinedAttributeLabels(),
            [
                //                'types' => Craft::t('organizations', 'Types'),
                //                'activeType' => Craft::t('organizations', 'Active Type'),
                //                'primaryType' => Craft::t('organizations', 'Primary Type'),
                //                'users' => Craft::t('organizations', 'Users'),
            ]
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
        if (!$type = $this->getActiveType()) {
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
        $organizationTypes = OrganizationType::findAll();

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
        $organizationUsers = OrganizationPlugin::getInstance()->getUsers()->getQuery();

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

        // Edit
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => EditAction::class,
            'label' => Craft::t('organizations', 'Edit organization'),
        ]);

//        if (Craft::$app->getUser()->checkPermission('deleteOrganizations')) {
//            // Delete
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
                $this->setUsers($users);
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
                foreach ($this->getTypes()->all() as $type) {
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
     * @throws Exception
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
        if (false === $this->associateTypes($this->getTypes())) {
            throw new Exception("Unable to save types.");
        }

        // Users
        if (false === $this->associateUsers($this->getUsers())) {
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

        // Populate the record attributes
        $record->id = $this->getId();
        $record->dateJoined = $this->getDateJoined();

        return $record;
    }


    /*******************************************
     * TYPES - ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param OrganizationTypeQuery $query
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function associateTypes(OrganizationTypeQuery $query): bool
    {
        $currentAssociations = OrganizationTypeAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('typeId')
            ->all();

        $success = true;

        // Delete
        if (null === ($types = $query->getCachedResult())) {
            foreach ($currentAssociations as $currentAssociation) {
                if (!$currentAssociation->delete()) {
                    $success = false;
                }
            }

            if (!$success) {
                $this->addError('types', 'Unable to dissociate types.');
            }

            return $success;
        }

        $associations = [];
        $order = 1;
        foreach ($types as $type) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $type->getId()))) {
                $association = (new OrganizationTypeAssociation())
                    ->setType($type)
                    ->setOrganization($this);
            }

            $association->sortOrder = $order++;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($currentAssociations as $currentAssociation) {
            if (!$currentAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($associations as $association) {
            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('types', 'Unable to associate types.');
        }

        $this->setTypes($query);

        return $success;
    }

    /*******************************************
     * USERS - ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param UserQuery $query
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveUsers(UserQuery $query)
    {
        $currentAssociations = UserAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('userId')
            ->all();

        $success = true;

        // Delete
        if (null === ($users = $query->getCachedResult())) {
            foreach ($currentAssociations as $currentAssociation) {
                if (!$currentAssociation->delete()) {
                    $success = false;
                }
            }

            if (!$success) {
                $this->addError('types', 'Unable to dissociate users.');
            }

            return $success;
        }

        $associations = [];
        $order = 1;
        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($user)
                    ->setOrganization($this);
            }

            $association->userOrder = $order++;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($currentAssociations as $currentAssociation) {
            if (!$currentAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($associations as $association) {
            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        $this->setUsers($query);

        return $success;
    }

    /**
     * @param UserQuery $query
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function associateUsers(UserQuery $query)
    {
        if (null === ($users = $query->getCachedResult())) {
            return true;
        }

        $success = true;
        $currentAssociations = UserAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('userId')
            ->all();

        $order = 1;
        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($user)
                    ->setOrganization($this);

                $association->userOrder = $order++;
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        $this->resetUsers();

        return $success;
    }

    /**
     * @param UserQuery $query
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociateUsers(UserQuery $query)
    {
        if (null === ($users = $query->getCachedResult())) {
            return true;
        }

        $currentAssociations = UserAssociation::find()
            ->organizationId($this->getId() ?: false)
            ->indexBy('userId')
            ->all();

        $success = true;

        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                continue;
            }

            if (!$association->delete()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        $this->setUsers($query);

        return $success;
    }
}
