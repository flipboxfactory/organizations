<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub;
use Craft;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\db\UserOrganizationAssociationQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\models\Settings;
use flipbox\organizations\models\SiteSettings;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\OrganizationTypeSiteSettings;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\services\Element;
use flipbox\organizations\services\UserOrganizationAssociations;
use flipbox\organizations\services\Users;

class ElementTest extends Unit
{
    /**
     * @var Element
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('element'))
            ->getElement();
    }

    /**
     * @throws \Exception
     */
    public function testBeforeSave()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        $this->service->beforeSave($org);

        $this->assertNotNull(
            $org->getDateJoined()
        );

        $dateTime = DateTimeHelper::currentUTCDateTime();
        $org->setDateJoined($dateTime);

        $this->assertEquals(
            $dateTime,
            $org->getDateJoined()
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetUriFormatWithNoUrls()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        $settings = $this->make(
            SiteSettings::class,
            [
                'hasUrls' => Expected::once(false)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once($settings)
            ]
        );

        $this->assertNull(
            $service->getUriFormat($org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetUriFormatWithUrls()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        $uriFormat = 'foo/bar';

        $settings = $this->make(
            SiteSettings::class,
            [
                'hasUrls' => Expected::once(true),
                'getUriFormat' => Expected::once($uriFormat)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once($settings)
            ]
        );

        $this->assertEquals(
            $uriFormat,
            $service->getUriFormat($org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetUriFormatFromDisabledSite()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once()
            ]
        );

        $this->assertNull(
            $service->getUriFormat($org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetRouteForNonActiveOrganizations()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class,
            [
                'getStatus' => Expected::once(Organization::STATUS_ARCHIVED)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class
        );

        $this->assertNull($service->getRoute($org));
    }

    /**
     * @throws \Exception
     */
    public function testGetRouteForDisabledSiteOrganizations()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class,
            [
                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once()
            ]
        );

        $this->assertNull($service->getRoute($org));
    }

    /**
     * @throws \Exception
     */
    public function testGetRouteForActiveOrganizations()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class,
            [
                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
            ]
        );

        $settings = $this->make(
            SiteSettings::class,
            [
                'hasUrls' => Expected::once(false)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once($settings)
            ]
        );

        $this->assertNull($service->getRoute($org));
    }
}
