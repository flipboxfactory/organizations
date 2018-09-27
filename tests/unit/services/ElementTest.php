<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\helpers\DateTimeHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\models\SiteSettings;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\services\Element;
use flipbox\organizations\services\Records;
use yii\base\Exception;

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
    public function testGetRouteForActiveOrganizationWithoutUrls()
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

    /**
     * @throws \Exception
     */
    public function testGetRouteForActiveOrganizationWithUrls()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class,
            [
                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
            ]
        );

        $template = 'foo/bar';

        $settings = $this->make(
            SiteSettings::class,
            [
                'hasUrls' => Expected::once(true),
                'getTemplate' => Expected::once($template)
            ]
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'getSiteSettings' => Expected::once($settings)
            ]
        );

        $this->assertArraySubset(
            [
                'templates/render',
                [
                    'template' => $template,
                    'variables' => []
                ]
            ],
            $service->getRoute($org)
        );
    }

    /**
     * @expectedException Exception
     */
    public function testAfterSaveFailSave()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'save' => Expected::once(false)
            ]
        );

        $service->afterSave($org, false);

        $this->doesNotPerformAssertions();
    }

    /**
     * @expectedException Exception
     */
    public function testAfterSaveFailAssociateTypes()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'save' => Expected::once(true),
                'associateTypes' => Expected::once(false)
            ]
        );

        $service->afterSave($org, false);

        $this->doesNotPerformAssertions();
    }

    /**
     * @expectedException Exception
     */
    public function testAfterSaveFailAssociateUsers()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'save' => Expected::once(true),
                'associateTypes' => Expected::once(true),
                'associateUsers' => Expected::once(false)
            ]
        );

        $service->afterSave($org, false);

        $this->doesNotPerformAssertions();
    }

    /**
     * @throws \Exception
     */
    public function testSaveSuccess()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();

        $record->method('attributes')->willReturn([
            'dateCreated',
            'dateUpdated'
        ]);

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'elementToRecord' => Expected::once($record)
            ]
        );

        $recordService = $this->make(
            Records::class,
            [
                'save' => true
            ]
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getRecords' => Expected::once($recordService)
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'save'
        );
        $method->setAccessible(true);

        $result = $method->invoke($service, $org, false);

        $this->assertTrue($result);
    }

    /**
     * @throws \Exception
     */
    public function testSaveFail()
    {
        /** @var Organization $org */
        $org = $this->make(
            Organization::class
        );

        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();

        $record->method('attributes')->willReturn([
            'dateCreated',
            'dateUpdated'
        ]);

        /** @var Element $service */
        $service = $this->make(
            Element::class,
            [
                'elementToRecord' => Expected::once($record)
            ]
        );

        $recordService = $this->make(
            Records::class,
            [
                'save' => false
            ]
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getRecords' => Expected::once($recordService)
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'save'
        );
        $method->setAccessible(true);

        $result = $method->invoke($service, $org, false);

        $this->assertFalse($result);
    }
}
