<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\Organization as OrganizationRecord;
use yii\base\Component;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Element extends Component
{
    /**
     * @param OrganizationElement $organization
     */
    public function beforeSave(OrganizationElement $organization)
    {
        if (empty($organization->getDateJoined())) {
            $organization->setDateJoined(DateTimeHelper::currentUTCDateTime());
        }
    }

    /**
     * Returns the URI format used to generate this element’s URI.
     *
     * @param OrganizationElement $organization
     * @return string|null
     */
    public function getUriFormat(OrganizationElement $organization)
    {
        if (null === ($siteSettings = $this->getSiteSettings($organization))) {
            return null;
        }

        if (!$siteSettings->hasUrls()) {
            return null;
        }

        return $siteSettings->getUriFormat();
    }

    /**
     * Returns the route that should be used when the element’s URI is requested.
     *
     * @param OrganizationElement $organization
     * @return mixed The route that the request should use, or null if no special action should be taken
     */
    public function getRoute(OrganizationElement $organization)
    {
        if (in_array(
            $organization->getStatus(),
            [OrganizationElement::STATUS_DISABLED, OrganizationElement::STATUS_ARCHIVED],
            true
        )) {
            return null;
        }

        if (null === ($siteSettings = $this->getSiteSettings($organization))) {
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
     * @param OrganizationElement $organization
     * @param bool $isNew
     * @throws Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function afterSave(OrganizationElement $organization, bool $isNew)
    {
        if (false === $this->save($organization, $isNew)) {
            throw new Exception('Unable to save organization record');
        }

        // Types
        if (!$this->associateTypes($organization)) {
            throw new Exception("Unable to save types.");
        }

        // Users
        if (!$this->associateUsers($organization)) {
            throw new Exception("Unable to save users.");
        }
    }

    /**
     * @param OrganizationElement $organization
     * @param bool $isNew
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function save(OrganizationElement $organization, bool $isNew): bool
    {
        $record = $this->elementToRecord($organization);

        if (!OrganizationPlugin::getInstance()->getRecords()->save($record)) {
            $organization->addErrors($record->getErrors());

            OrganizationPlugin::error(
                Json::encode($organization->getErrors()),
                __METHOD__
            );

            return false;
        }

        if (false !== ($dateUpdated = DateTimeHelper::toDateTime($record->dateUpdated))) {
            $organization->dateUpdated = $dateUpdated;
        }


        if ($isNew) {
            $organization->id = $record->id;

            if (false !== ($dateCreated = DateTimeHelper::toDateTime($record->dateCreated))) {
                $organization->dateCreated = $dateCreated;
            }
        }

        return true;
    }

    /*******************************************
     * MODEL TO RECORD
     *******************************************/

    /**
     * @inheritdoc
     * @param OrganizationElement $organization
     * @return OrganizationRecord
     */
    protected function elementToRecord(OrganizationElement $organization): OrganizationRecord
    {
        $activeRecordService = OrganizationPlugin::getInstance()->getRecords();

        if (!$record = $activeRecordService->findByCondition([
            'id' => $organization->id
        ])) {
            $record = $activeRecordService->create();
        }

        // Populate the record attributes
        $record->id = $organization->getId();
        $record->dateJoined = $organization->dateJoined;

        return $record;
    }

    /**
     * @param OrganizationElement $organization
     * @return \flipbox\organizations\records\OrganizationTypeSiteSettings|null
     */
    protected function getSiteSettings(OrganizationElement $organization)
    {
        try {
            $settings = OrganizationPlugin::getInstance()->getSettings();
            $siteSettings = $settings->getSiteSettings()[$organization->siteId] ?? null;

            if (null !== ($type = $organization->getPrimaryType())) {
                $siteSettings = $type->getSiteSettings()[$organization->siteId] ?? $siteSettings;
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

    /*******************************************
     * TYPES - ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    private function associateTypes(OrganizationElement $organization)
    {
        if (!OrganizationPlugin::getInstance()->getOrganizationTypes()->saveAssociations(
            $organization->getTypes(),
            $organization
        )) {
            $organization->addError(
                'types',
                Craft::t('organizations', 'Unable to save types.')
            );

            return false;
        }

        // Reset cache
        $organization->resetTypes();

        return true;
    }

    /*******************************************
     * USERS - ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    private function associateUsers(OrganizationElement $organization)
    {
        if (!OrganizationPlugin::getInstance()->getOrganizations()->saveAssociations(
            $organization->getUsers(),
            $organization
        )) {
            $organization->addError(
                'users',
                Craft::t('organizations', 'Unable to save users.')
            );

            return false;
        }

        // Reset cache
        $organization->resetUsers();

        return true;
    }
}
