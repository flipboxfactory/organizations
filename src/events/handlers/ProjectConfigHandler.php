<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use Craft;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\models\FieldLayout;
use flipbox\organizations\events\ManageOrganizationTypeProjectConfig;
use flipbox\organizations\events\ManageUserTypeProjectConfig;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\UserType;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class ProjectConfigHandler
{
    /**
     * @param ConfigEvent $event
     */
    public static function handleChangedOrganizationType(ConfigEvent $event)
    {
        Event::off(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_INSERT,
            [
                ManageOrganizationTypeProjectConfig::class,
                'save'
            ]
        );

        Event::off(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_UPDATE,
            [
                ManageOrganizationTypeProjectConfig::class,
                'save'
            ]
        );

        Event::off(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_INSERT,
            [
                ManageOrganizationTypeProjectConfig::class,
                'save'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === ($type = OrganizationType::findOne([
                'uid' => $uid
            ]))
        ) {
            $type = new OrganizationType();
        }

        // Field Layout
        if (isset($event->newValue['fieldLayout'])) {
            $event->newValue['fieldLayout'] = static::createFieldLayout($event->newValue['fieldLayout'] ?? []);
        }

        // Sites
        if (isset($event->newValue['siteSettings'])) {
            $siteSettings = [];
            foreach((array)$event->newValue['siteSettings'] as $id => $settings) {
                // id may be a uid
                if (!is_numeric($id)) {
                    $uid = $id;
                    $id = Db::idByUid(Table::SITES, $uid);
                }

                $settings['typeId'] = $type->getId();

                if (!empty($uid)) {
                    $settings['uid'] = $uid;
                }

                $siteSettings[$id] = $settings;
            }
            $event->newValue['siteSettings'] = $siteSettings;
        }

        Craft::configure($type, $event->newValue);

        $type->save();

        Event::on(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_INSERT,
            [
                ManageOrganizationTypeProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_UPDATE,
            [
                ManageOrganizationTypeProjectConfig::class,
                'save'
            ]
        );
    }

    /**
     * @param array $config
     * @return FieldLayout
     */
    protected static function createFieldLayout(array $config = [])
    {
        $fieldLayout = FieldLayout::createFromConfig($config);

        if (isset($config['uid'])) {
            $fieldLayout->uid = $config['uid'];
            $fieldLayout->id = Db::idByUid(Table::FIELDLAYOUTS, $config['uid']);
        }

        return $fieldLayout;
    }

    /**
     * @param ConfigEvent $event
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function handleDeletedOrganizationType(ConfigEvent $event)
    {
        Event::off(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_DELETE,
            [
                ManageOrganizationTypeProjectConfig::class,
                'delete'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === $type = OrganizationType::findOne([
                'uid' => $uid
            ])) {
            return;
        }

        $type->delete();

        Event::on(
            OrganizationType::class,
            OrganizationType::EVENT_AFTER_DELETE,
            [
                ManageOrganizationTypeProjectConfig::class,
                'delete'
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public static function handleChangedUserType(ConfigEvent $event)
    {
        Event::off(
            UserType::class,
            UserType::EVENT_AFTER_INSERT,
            [
                ManageUserTypeProjectConfig::class,
                'save'
            ]
        );

        Event::off(
            UserType::class,
            UserType::EVENT_AFTER_UPDATE,
            [
                ManageUserTypeProjectConfig::class,
                'save'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === ($token = UserType::findOne([
                'uid' => $uid
            ]))) {
            $token = new UserType();
        }

        Craft::configure($token, $event->newValue);

        $token->save();

        Event::on(
            UserType::class,
            UserType::EVENT_AFTER_INSERT,
            [
                ManageUserTypeProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            UserType::class,
            UserType::EVENT_AFTER_UPDATE,
            [
                ManageUserTypeProjectConfig::class,
                'save'
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function handleDeletedUserType(ConfigEvent $event)
    {
        Event::off(
            UserType::class,
            UserType::EVENT_AFTER_DELETE,
            [
                ManageUserTypeProjectConfig::class,
                'delete'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === $token = UserType::findOne([
                'uid' => $uid
            ])) {
            return;
        }

        $token->delete();

        Event::on(
            UserType::class,
            UserType::EVENT_AFTER_DELETE,
            [
                ManageUserTypeProjectConfig::class,
                'delete'
            ]
        );
    }

    /**
     * @return array
     */
    public static function rebuild(): array
    {
        $return = [];

        foreach (OrganizationType::findAll([]) as $record) {
            $return['plugins']['organizations']['organizationTypes'][$record->uid] = $record->toProjectConfig();
        }

        foreach (UserType::findAll([]) as $token) {
            $return['plugins']['organizations']['userTypes'][$record->uid] = $record->toProjectConfig();
        }

        return $return;
    }
}
