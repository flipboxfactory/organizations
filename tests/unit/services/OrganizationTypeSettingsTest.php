<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\queue\Queue;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeSiteSettings;
use flipbox\organizations\services\OrganizationTypeSettings;
use yii\db\ActiveQuery;

class OrganizationTypeSettingsTest_z extends Unit
{
//
//    /**
//     * @var OrganizationTypeSettings
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
//            ->getOrganizationTypeSettings();
//    }
//
//    /**
//     *
//     */
//    public function testRecordClass()
//    {
//        $this->assertEquals(
//            OrganizationTypeSiteSettings::class,
//            $this->service::recordClass()
//        );
//    }
//
//    /**
//     *
//     */
//    public function testSaveByTypeSuccess()
//    {
//        $settings = $this->getMockBuilder(OrganizationTypeSiteSettings::class)
//            ->setMethods(['attributes', 'save', 'delete'])
//            ->getMock();
//
//        $settings->method('attributes')->willReturn([
//            'siteId',
//            'typeId'
//        ]);
//
//        $settings->siteId = 1;
//        $settings->typeId = 1;
//
//        $settings->expects($this->once())
//            ->method('delete')
//            ->willReturn(true);
//
//        $settings->expects($this->once())
//            ->method('save')
//            ->willReturn(true);
//
//        $query = $this->getMockBuilder(ActiveQuery::class)
//            ->setConstructorArgs([
//                OrganizationType::class
//            ])
//            ->setMethods(['indexBy', 'all'])
//            ->getMock();
//
//        $query->expects($this->once())
//            ->method('indexBy')
//            ->willReturn($query);
//
//        $query->expects($this->once())
//            ->method('all')
//            ->willReturn([2 => $settings]);
//
//        $record = $this->getMockBuilder(OrganizationType::class)
//            ->setMethods(['getSiteSettings', 'hasMany', 'getId'])
//            ->getMock();
//
//        $record->expects($this->once())
//            ->method('getSiteSettings')
//            ->willReturn([1 => $settings]);
//
//        $record->expects($this->once())
//            ->method('getId')
//            ->willReturn(1);
//
//        $record->expects($this->once())
//            ->method('hasMany')
//            ->with(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
//            ->willReturn($query);
//
//        $service = $this->make(
//            $this->service,
//            [
//                'reSaveOrganizations' => Expected::once(true)
//            ]
//        );
//
//        $this->assertTrue(
//            $service->saveByType($record)
//        );
//    }
//
//    /**
//     *
//     */
//    public function testSaveByTypeFail()
//    {
//        $settings = $this->getMockBuilder(OrganizationTypeSiteSettings::class)
//            ->setMethods(['attributes', 'save', 'delete', 'getFirstErrors'])
//            ->getMock();
//
//        $settings->method('attributes')->willReturn([
//            'siteId',
//            'typeId'
//        ]);
//
//        $settings->siteId = 1;
//        $settings->typeId = 1;
//
//        $settings->expects($this->once())
//            ->method('delete')
//            ->willReturn(false);
//
//        $settings->expects($this->once())
//            ->method('getFirstErrors')
//            ->willReturn([
//                'foo' => 'bar'
//            ]);
//
//        $settings->expects($this->once())
//            ->method('save')
//            ->willReturn(false);
//
//        $query = $this->getMockBuilder(ActiveQuery::class)
//            ->setConstructorArgs([
//                OrganizationType::class
//            ])
//            ->setMethods(['indexBy', 'all'])
//            ->getMock();
//
//        $query->expects($this->once())
//            ->method('indexBy')
//            ->willReturn($query);
//
//        $query->expects($this->once())
//            ->method('all')
//            ->willReturn([2 => $settings]);
//
//        $record = $this->getMockBuilder(OrganizationType::class)
//            ->setMethods(['getSiteSettings', 'hasMany', 'getId'])
//            ->getMock();
//
//        $record->expects($this->once())
//            ->method('getSiteSettings')
//            ->willReturn([1 => $settings]);
//
//        $record->expects($this->once())
//            ->method('getId')
//            ->willReturn(1);
//
//        $record->expects($this->once())
//            ->method('hasMany')
//            ->with(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
//            ->willReturn($query);
//
//        $service = $this->make(
//            $this->service,
//            [
//                'reSaveOrganizations' => Expected::once(true)
//            ]
//        );
//
//        $this->assertFalse(
//            $service->saveByType($record)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAfterSaveInsert()
//    {
//
//        $insert = true;
//
//        $attributes = [
//            'uriFormat' => true,
//            'hasUrls' => true
//        ];
//
//        $type = $this->make(
//            OrganizationTypeSiteSettings::class,
//            [
//                'getAttribute' => Expected::never(),
//                'getOldAttribute' => Expected::never()
//            ]
//        );
//
//        $this->service->afterSave(
//            $type,
//            $insert,
//            $attributes
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAfterSaveNoUri()
//    {
//
//        $insert = false;
//
//        $attributes = [
//            'hasUrls' => true
//        ];
//
//        $type = $this->getMockBuilder(OrganizationTypeSiteSettings::class)
//            ->setMethods(['getAttribute', 'getOldAttribute'])
//            ->getMock();
//
//        $type->expects($this->once())
//            ->method('getAttribute')
//            ->with('hasUrls')
//            ->willReturn(true);
//
//        $type->expects($this->once())
//            ->method('getOldAttribute')
//            ->with('hasUrls')
//            ->willReturn(false);
//
//        $service = $this->make(
//            $this->service,
//            [
//                'reSaveOrganizations' => Expected::once()
//            ]
//        );
//
//        $service->afterSave(
//            $type,
//            $insert,
//            $attributes
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     * @throws \yii\base\InvalidConfigException
//     */
//    public function testReSaveOrganizations()
//    {
//        \Craft::$app->set(
//            'queue',
//            $this->make(
//                Queue::class,
//                [
//                    'push' => Expected::once(1)
//                ]
//            )
//        );
//
//        $type = $this->make(
//            OrganizationTypeSiteSettings::class,
//            [
//                'getSiteId' => Expected::exactly(2, 1),
//                'getTypeId' => Expected::once(1)
//            ]
//        );
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'reSaveOrganizations'
//        );
//        $method->setAccessible(true);
//
//        $method->invoke($this->service, $type);
//    }
}