<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use yii\base\Exception;

class ElementTest_z extends Unit
{
//    /**
//     * @var Element
//     */
//    private $service;
//
//    /**
//     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
//     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
//     */
//    protected function _before()
//    {
//        $this->service = (new OrganizationsPlugin('organizations'))
//            ->getElement();
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testBeforeSave()
//    {
//        $dateTime = DateTimeHelper::currentUTCDateTime();
//
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'setDateJoined' => Expected::never(),
//                'getDateJoined' => Expected::once($dateTime)
//            ]
//        );
//
//        $this->service->beforeSave($org);
//
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testBeforeSaveNullJoin()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'setDateJoined' => Expected::once(),
//                'getDateJoined' => Expected::once()
//            ]
//        );
//
//        $this->service->beforeSave($org);
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetUriFormatWithNoUrls()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        $settings = $this->make(
//            SiteSettings::class,
//            [
//                'hasUrls' => Expected::once(false)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once($settings)
//            ]
//        );
//
//        $this->assertNull(
//            $service->getUriFormat($org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetUriFormatWithUrls()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        $uriFormat = 'foo/bar';
//
//        $settings = $this->make(
//            SiteSettings::class,
//            [
//                'hasUrls' => Expected::once(true),
//                'getUriFormat' => Expected::once($uriFormat)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once($settings)
//            ]
//        );
//
//        $this->assertEquals(
//            $uriFormat,
//            $service->getUriFormat($org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetUriFormatFromDisabledSite()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once()
//            ]
//        );
//
//        $this->assertNull(
//            $service->getUriFormat($org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetRouteForNonActiveOrganizations()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getStatus' => Expected::once(Organization::STATUS_ARCHIVED)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $this->assertNull($service->getRoute($org));
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetRouteForDisabledSiteOrganizations()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once()
//            ]
//        );
//
//        $this->assertNull($service->getRoute($org));
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetRouteForActiveOrganizationWithoutUrls()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
//            ]
//        );
//
//        $settings = $this->make(
//            SiteSettings::class,
//            [
//                'hasUrls' => Expected::once(false)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once($settings)
//            ]
//        );
//
//        $this->assertNull($service->getRoute($org));
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetRouteForActiveOrganizationWithUrls()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getStatus' => Expected::once(Organization::STATUS_ENABLED)
//            ]
//        );
//
//        $template = 'foo/bar';
//
//        $settings = $this->make(
//            SiteSettings::class,
//            [
//                'hasUrls' => Expected::once(true),
//                'getTemplate' => Expected::once($template)
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'getSiteSettings' => Expected::once($settings)
//            ]
//        );
//
//        $this->assertArraySubset(
//            [
//                'templates/render',
//                [
//                    'template' => $template,
//                    'variables' => []
//                ]
//            ],
//            $service->getRoute($org)
//        );
//    }
//
//    /**
//     * @throws Exception
//     * @throws \Throwable
//     * @expectedException Exception
//     */
//    public function testAfterSaveFailSave()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'save' => Expected::once(false)
//            ]
//        );
//
//        $service->afterSave($org, false);
//
//        $this->doesNotPerformAssertions();
//    }
//
//    /**
//     * @throws Exception
//     * @throws \Throwable
//     * @expectedException Exception
//     */
//    public function testAfterSaveFailAssociateTypes()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'save' => Expected::once(true),
//                'associateTypes' => Expected::once(false)
//            ]
//        );
//
//        $service->afterSave($org, false);
//
//        $this->doesNotPerformAssertions();
//    }
//
//    /**
//     * @throws Exception
//     * @expectedException Exception
//     */
//    public function testAfterSaveFailAssociateUsers()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'save' => Expected::once(true),
//                'associateTypes' => Expected::once(true),
//                'associateUsers' => Expected::once(false)
//            ]
//        );
//
//        $service->afterSave($org, false);
//
//        $this->doesNotPerformAssertions();
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveSuccess()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
//            ->setMethods(['save', 'attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'dateCreated',
//            'dateUpdated'
//        ]);
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'elementToRecord' => Expected::once($record)
//            ]
//        );
//
//        $recordService = $this->make(
//            Records::class,
//            [
//                'save' => true
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getRecords' => Expected::once($recordService)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'save'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org, false);
//
//        $this->assertTrue($result);
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveFail()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
//            ->setMethods(['save', 'attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'dateCreated',
//            'dateUpdated'
//        ]);
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'elementToRecord' => Expected::once($record)
//            ]
//        );
//
//        $recordService = $this->make(
//            Records::class,
//            [
//                'save' => false
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getRecords' => Expected::once($recordService)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'save'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org, false);
//
//        $this->assertFalse($result);
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveSuccessNew()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class
//        );
//
//        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
//            ->setMethods(['save', 'attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'id',
//            'dateCreated',
//            'dateUpdated'
//        ]);
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class,
//            [
//                'elementToRecord' => Expected::once($record)
//            ]
//        );
//
//        $recordService = $this->make(
//            Records::class,
//            [
//                'save' => true
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getRecords' => Expected::once($recordService)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'save'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org, true);
//
//        $this->assertTrue($result);
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testElementToRecord()
//    {
//        $id = 1;
//        $dateJoined = DateTimeHelper::currentUTCDateTime();
//
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getId' => Expected::once($id),
//                'dateJoined' => $dateJoined
//            ]
//        );
//
//        $record = $this->getMockBuilder(\flipbox\organizations\records\Organization::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'id',
//            'dateJoined'
//        ]);
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $recordService = $this->make(
//            Records::class,
//            [
//                'findByCondition' => Expected::once(),
//                'create' => Expected::once($record)
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getRecords' => Expected::once($recordService)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'elementToRecord'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertInstanceOf(
//            \flipbox\organizations\records\Organization::class,
//            $result
//        );
//
//        $this->assertEquals(
//            $id,
//            $record->id
//        );
//
//        $this->assertEquals(
//            $dateJoined,
//            $record->dateJoined
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetSiteSettingsNull()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getPrimaryType' => Expected::once(
//                    $this->make(
//                        OrganizationType::class
//                    )
//                )
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getSettings' => Expected::once(
//                    $this->make(
//                        Settings::class,
//                        [
//                            'getSiteSettings' => Expected::once([])
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'getSiteSettings'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertNull(
//            $result
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetSiteSettings()
//    {
//        $siteId = 1;
//
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'siteId' => $siteId,
//                'getPrimaryType' => Expected::once()
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $settings = $this->make(
//            Settings::class,
//            [
//                'getSiteSettings' => Expected::once([
//                    $siteId => $this->make(
//                        SiteSettings::class
//                    )
//                ])
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getSettings' => Expected::once($settings)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'getSiteSettings'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertInstanceOf(
//            SiteSettings::class,
//            $result
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociateTypesSuccess()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getTypes' => Expected::once(
//                    $this->make(
//                        OrganizationTypeQuery::class
//                    )
//                )
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $typeService = $this->make(
//            OrganizationTypes::class,
//            [
//                'saveAssociations' => Expected::once(true)
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypes' => Expected::once($typeService)
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associateTypes'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertTrue(
//            $result
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociateTypesFail()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getTypes' => Expected::once(
//                    $this->make(
//                        OrganizationTypeQuery::class
//                    )
//                ),
//                'addError' => Expected::once()
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $typeService = $this->make(
//            OrganizationTypes::class,
//            [
//                'saveAssociations' => Expected::once(false)
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypes' => Expected::once($typeService)
//            ]
//        );
//
//        // Translation category
//        $t9nCategory = 'organizations';
//        $i18n = \Craft::$app->getI18n();
//        /** @noinspection UnSafeIsSetOverArrayInspection */
//        if (!isset($i18n->translations[$t9nCategory]) && !isset($i18n->translations[$t9nCategory . '*'])) {
//            $i18n->translations[$t9nCategory] = [
//                'class' => PhpMessageSource::class,
//                'forceTranslation' => true,
//                'allowOverrides' => true,
//            ];
//        }
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        \Craft::$app->plugins->init();
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associateTypes'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertFalse(
//            $result
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociateUsersSuccess()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getUsers' => Expected::once(
//                    $this->make(
//                        UserQuery::class
//                    )
//                )
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizations' => Expected::once(
//                    $this->make(
//                        \flipbox\organizations\services\Organizations::class,
//                        [
//                            'saveAssociations' => Expected::once(true)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associateUsers'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertTrue(
//            $result
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociateUsersFail()
//    {
//        /** @var Organization $org */
//        $org = $this->make(
//            Organization::class,
//            [
//                'getUsers' => Expected::once(
//                    $this->make(
//                        UserQuery::class
//                    )
//                ),
//                'addError' => Expected::once()
//            ]
//        );
//
//        /** @var Element $service */
//        $service = $this->make(
//            Element::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizations' => Expected::once(
//                    $this->make(
//                        \flipbox\organizations\services\Organizations::class,
//                        [
//                            'saveAssociations' => Expected::once(false)
//                        ]
//                    )
//                )
//            ]
//        );
//
//
//        // Translation category
//        $t9nCategory = 'organizations';
//        $i18n = \Craft::$app->getI18n();
//        /** @noinspection UnSafeIsSetOverArrayInspection */
//        if (!isset($i18n->translations[$t9nCategory]) && !isset($i18n->translations[$t9nCategory . '*'])) {
//            $i18n->translations[$t9nCategory] = [
//                'class' => PhpMessageSource::class,
//                'forceTranslation' => true,
//                'allowOverrides' => true,
//            ];
//        }
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        \Craft::$app->plugins->init();
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associateUsers'
//        );
//        $method->setAccessible(true);
//
//        $result = $method->invoke($service, $org);
//
//        $this->assertFalse(
//            $result
//        );
//    }
}
