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
use craft\elements\User;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\ember\helpers\ModelHelper;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\OrganizationType as TypeModel;
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
        $organizationTypes = OrganizationPlugin::getInstance()->getOrganizationTypes()->findAll();

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
        return OrganizationPlugin::getInstance()->getElement()->getUriFormat($this);
    }

    /**
     * @inheritdoc
     */
    public function route()
    {
        return OrganizationPlugin::getInstance()->getElement()->getRoute($this);
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
        OrganizationPlugin::getInstance()->getElement()->beforeSave($this);
        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        OrganizationPlugin::getInstance()->getElement()->afterSave($this, $isNew);
        parent::afterSave($isNew);
    }
}
