<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\models\FieldLayout;
use craft\services\Fields;
use flipbox\organizations\db\OrganizationTypeAssociationQuery;
use flipbox\organizations\db\OrganizationTypeQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\models\Settings;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationTypeAssociation;
use flipbox\organizations\services\OrganizationTypeAssociations;
use flipbox\organizations\services\OrganizationTypes;
use flipbox\organizations\services\OrganizationTypeSettings;
use yii\base\InvalidConfigException;

class OrganizationTypesTest_z extends Unit
{
//    /**
//     * @var OrganizationTypes
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
//            ->getOrganizationTypes();
//    }
//
//    /**
//     *
//     */
//    public function testRecordClass()
//    {
//        $this->assertEquals(
//            OrganizationType::class,
//            $this->service::recordClass()
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     */
//    public function testStringProperty()
//    {
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'stringProperty'
//        );
//        $method->setAccessible(true);
//
//        $this->assertEquals(
//            'handle',
//            $method->invoke($this->service)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolveFound()
//    {
//        /** @var OrganizationTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(
//                    $this->make(
//                        OrganizationType::class
//                    )
//                ),
//            ]
//        );
//
//        $this->assertInstanceOf(
//            OrganizationType::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolve()
//    {
//        /** @var OrganizationTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(),
//                'create' => Expected::once(
//                    $this->make(
//                        OrganizationType::class
//                    )
//                )
//            ]
//        );
//
//        $this->assertInstanceOf(
//            OrganizationType::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     */
//    public function testPrepareQueryConfig()
//    {
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'prepareQueryConfig'
//        );
//        $method->setAccessible(true);
//
//        $this->assertArraySubset(
//            ['with' => ['siteSettingRecords']],
//            $method->invoke($this->service)
//        );
//    }
//
//    /**
//     * @throws InvalidConfigException
//     * @throws \Exception
//     */
//    public function testBeforeSaveSuccess()
//    {
//        /** @var OrganizationType $type */
//        $type = $this->make(
//            OrganizationType::class,
//            [
//                'getFieldLayout' => $this->make(
//                    FieldLayout::class,
//                    [
//
//                    ]
//                )
//            ]
//        );
//
//        /** @var OrganizationTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'getDefaultFieldLayoutId' => Expected::once(1),
//            ]
//        );
//
//        \Craft::$app->set(
//            'fields',
//            $this->make(
//                Fields::class,
//                [
//                    'saveLayout' => Expected::once(true)
//                ]
//            )
//        );
//
//        $this->assertTrue(
//            $service->beforeSave($type)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testBeforeSaveMatch()
//    {
//        /** @var OrganizationType $type */
//        $type = $this->make(
//            OrganizationType::class,
//            [
//                'getFieldLayout' => $this->make(
//                    FieldLayout::class,
//                    [
//                        'id' => 1
//                    ]
//                )
//            ]
//        );
//
//        /** @var OrganizationTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'getDefaultFieldLayoutId' => Expected::exactly(2, 1),
//            ]
//        );
//
//        $this->assertTrue(
//            $service->beforeSave($type)
//        );
//    }
//
//    /**
//     * @throws InvalidConfigException
//     * @throws \Exception
//     */
//    public function testBeforeSaveFail()
//    {
//        /** @var OrganizationType $type */
//        $type = $this->make(
//            OrganizationType::class,
//            [
//                'getFieldLayout' => $this->make(
//                    FieldLayout::class,
//                    [
//
//                    ]
//                )
//            ]
//        );
//
//        /** @var OrganizationTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'getDefaultFieldLayoutId' => Expected::once(1),
//            ]
//        );
//
//        \Craft::$app->set(
//            'fields',
//            $this->make(
//                Fields::class,
//                [
//                    'saveLayout' => Expected::once(false)
//                ]
//            )
//        );
//
//        $this->assertFalse(
//            $service->beforeSave($type)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testGetDefaultFieldLayoutId()
//    {
//        $id = 1;
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getSettings' => Expected::once(
//                    $this->make(
//                        Settings::class,
//                        [
//                            'getFieldLayout' => Expected::once(
//                                $this->make(
//                                    FieldLayout::class,
//                                    [
//                                        'id' => $id
//                                    ]
//                                )
//                            )
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
//            $this->service,
//            'getDefaultFieldLayoutId'
//        );
//        $method->setAccessible(true);
//
//        $this->assertEquals(
//            $id,
//            $method->invoke($this->service)
//        );
//    }
//
//    /**
//     * @throws \Throwable
//     * @throws \yii\base\Exception
//     * @throws \yii\db\StaleObjectException
//     */
//    public function testAfterSaveSuccess()
//    {
//        /** @var OrganizationType $type */
//        $type = $this->make(
//            OrganizationType::class,
//            [
//                'getFieldLayout' => $this->make(
//                    FieldLayout::class,
//                    [
//
//                    ]
//                )
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypeSettings' => Expected::once(
//                    $this->make(
//                        OrganizationTypeSettings::class,
//                        [
//                            'saveByType' => Expected::once(true)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        $this->service->afterSave($type);
//    }
//
//    /**
//     * @throws \Throwable
//     * @throws \yii\base\Exception
//     * @throws \yii\db\StaleObjectException
//     * @expectedException \Exception
//     */
//    public function testAfterSaveFail()
//    {
//        /** @var OrganizationType $type */
//        $type = $this->make(
//            OrganizationType::class,
//            [
//                'getFieldLayout' => $this->make(
//                    FieldLayout::class,
//                    [
//
//                    ]
//                )
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypeSettings' => Expected::once(
//                    $this->make(
//                        OrganizationTypeSettings::class,
//                        [
//                            'saveByType' => Expected::once(false)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        $this->service->afterSave($type);
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveAssociationsWithNoCachedResult()
//    {
//        $query = $this->make(
//            OrganizationTypeQuery::class,
//            [
//                'getCachedResult' => Expected::once()
//            ]
//        );
//
//        $org = $this->make(
//            Organization::class
//        );
//
//        $this->assertTrue(
//            $this->service->saveAssociations($query, $org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveAssociationsWithCachedResult()
//    {
//        $id = 1;
//
//        $org = $this->make(
//            Organization::class,
//            [
//                'id' => $id,
//                'getId' => Expected::exactly(2, $id)
//            ]
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class,
//            [
//                'organizationId' => $id,
//                'getCachedResult' => Expected::once([$org])
//            ]
//        );
//
//        $service = $this->make(
//            $this->service,
//            [
//                'toAssociations' => Expected::once([
//                    $this->make(OrganizationTypeAssociation::class)
//                ])
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypeAssociations' => Expected::once(
//                    $this->make(
//                        OrganizationTypeAssociations::class,
//                        [
//                            'getQuery' => Expected::once(
//                                $this->make(
//                                    OrganizationTypeAssociationQuery::class
//                                )
//                            ),
//                            'save' => Expected::once(true)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        $this->assertTrue(
//            $service->saveAssociations($query, $org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testDissociate()
//    {
//        $org = $this->make(
//            Organization::class
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class
//        );
//
//        $service = $this->make($this->service, [
//            'associations' => Expected::once(
//                true
//            )
//        ]);
//
//        $this->assertTrue(
//            $service->dissociate($query, $org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociate()
//    {
//        $org = $this->make(
//            Organization::class
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class
//        );
//
//        $service = $this->make($this->service, [
//            'associations' => Expected::once(
//                true
//            )
//        ]);
//
//        $this->assertTrue(
//            $service->associate($query, $org)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationsWithEmptyCache()
//    {
//
//        $org = $this->make(
//            Organization::class
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class
//        );
//
//        $assService = $this->make(
//            OrganizationTypeAssociations::class
//        );
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'associations'
//        );
//        $method->setAccessible(true);
//
//        $this->assertTrue(
//            $method->invoke(
//                $this->service,
//                $query,
//                $org,
//                [
//                    $assService,
//                    'associate'
//                ]
//            )
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationsWithCache()
//    {
//        $org = $this->make(
//            Organization::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class,
//            [
//                'getCachedResult' => Expected::once([$org]),
//                'organizationTypeId' => Expected::once()
//            ]
//        );
//
//        $assService = $this->make(
//            OrganizationTypeAssociations::class,
//            [
//                'associate' => Expected::once(true)
//            ]
//        );
//
//        $record = $this->getMockBuilder(OrganizationTypeAssociation::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'typeId'
//        ]);
//
//        $service = $this->make(
//            $this->service,
//            [
//                'toAssociations' => Expected::once([$record])
//            ]
//        );
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associations'
//        );
//        $method->setAccessible(true);
//
//        $this->assertTrue(
//            $method->invoke(
//                $service,
//                $query,
//                $org,
//                [
//                    $assService,
//                    'associate'
//                ]
//            )
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationsWithCacheNoAssociations()
//    {
//        $org = $this->make(
//            Organization::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $query = $this->make(
//            OrganizationTypeQuery::class,
//            [
//                'getCachedResult' => Expected::once([$org]),
//                'setCachedResult' => Expected::once(),
//                'organizationTypeId' => Expected::once()
//            ]
//        );
//
//        $assService = $this->make(
//            OrganizationTypeAssociations::class,
//            [
//                'associate' => Expected::once(false)
//            ]
//        );
//
//        $record = $this->getMockBuilder(OrganizationTypeAssociation::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'typeId'
//        ]);
//
//        $service = $this->make(
//            $this->service,
//            [
//                'toAssociations' => Expected::once([$record])
//            ]
//        );
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $service,
//            'associations'
//        );
//        $method->setAccessible(true);
//
//        $this->assertFalse(
//            $method->invoke(
//                $service,
//                $query,
//                $org,
//                [
//                    $assService,
//                    'associate'
//                ]
//            )
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testToAssociations()
//    {
//        $id = 1;
//
//        $record = $this->getMockBuilder(OrganizationType::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'id'
//        ]);
//
//        $types = [
//            $record
//        ];
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'toAssociations'
//        );
//        $method->setAccessible(true);
//
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationTypeAssociations' => Expected::once(
//                    $this->make(
//                        OrganizationTypeAssociations::class,
//                        [
//                            'create' => Expected::once(
//                                $this->make(
//                                    OrganizationTypeAssociation::class
//                                )
//                            )
//                        ]
//                    )
//                )
//            ]
//        );
//
//        \Craft::$app->loadedModules[Organizations::class] = $plugin;
//        \Yii::$app->loadedModules[Organizations::class] = $plugin;
//
//        $method->invoke(
//            $this->service,
//            $types,
//            $id
//        );
//    }
}
