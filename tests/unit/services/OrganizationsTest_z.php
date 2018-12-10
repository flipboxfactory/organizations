<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\db\UserQuery;
use craft\models\Site;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\services\OrganizationUsers;

class OrganizationsTest_z extends Unit
{

//    /**
//     * @var \flipbox\organizations\services\Organizations
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
//            ->getOrganizations();
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function testElementClass()
//    {
//        $this->assertEquals(
//            Organization::class,
//            $this->service::elementClass()
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     */
//    public function testIdentifierConditionAsId()
//    {
//        $siteId = 1;
//        $status = null;
//        $identifier = '1';
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'identifierCondition'
//        );
//        $method->setAccessible(true);
//
//        \Craft::$app->getSites()->setCurrentSite(
//            new Site([
//                'id' => $siteId
//            ])
//        );
//
//        $this->assertArraySubset(
//            [
//                'siteId' => $siteId,
//                'status' => $status,
//                'id' => $identifier
//            ],
//            $method->invoke($this->service, $identifier)
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     */
//    public function testIdentifierConditionAsSlug()
//    {
//        $siteId = 1;
//        $status = null;
//        $identifier = 'flipbox';
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'identifierCondition'
//        );
//        $method->setAccessible(true);
//
//        \Craft::$app->getSites()->setCurrentSite(
//            new Site([
//                'id' => $siteId
//            ])
//        );
//
//        $this->assertArraySubset(
//            [
//                'siteId' => $siteId,
//                'status' => $status,
//                'slug' => $identifier
//            ],
//            $method->invoke($this->service, $identifier)
//        );
//    }
//
//    /**
//     * @throws \ReflectionException
//     */
//    public function testIdentifierConditionAsArray()
//    {
//        $siteId = 1;
//        $status = null;
//        $identifier = [
//            'slug' => 'flipbox',
//            'status' => Organization::STATUS_ARCHIVED
//        ];
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'identifierCondition'
//        );
//        $method->setAccessible(true);
//
//        \Craft::$app->getSites()->setCurrentSite(
//            new Site([
//                'id' => $siteId
//            ])
//        );
//
//        $this->assertArraySubset(
//            array_merge(
//                [
//                    'siteId' => $siteId,
//                    'status' => $status
//                ],
//                $identifier
//            ),
//            $method->invoke($this->service, $identifier)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolveFound()
//    {
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(
//                    $this->make(
//                        Organization::class
//                    )
//                ),
//            ]
//        );
//
//        $this->assertInstanceOf(
//            Organization::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolveArray()
//    {
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(
//                    $this->make(
//                        Organization::class
//                    )
//                ),
//            ]
//        );
//
//        $this->assertInstanceOf(
//            Organization::class,
//            $service->resolve(['id' => 1])
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolveCreate()
//    {
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(),
//                'create' => Expected::once(
//                    $this->make(
//                        Organization::class
//                    )
//                )
//            ]
//        );
//
//        $this->assertInstanceOf(
//            Organization::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveAssociations()
//    {
//        $org = $this->make(
//            Organization::class
//        );
//
//        $query = $this->make(
//            UserQuery::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationUsers' => Expected::once(
//                    $this->make(
//                        OrganizationUsers::class,
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
//        $this->assertTrue(
//            $this->service->saveAssociations($query, $org)
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
//            UserQuery::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationUsers' => Expected::once(
//                    $this->make(
//                        OrganizationUsers::class,
//                        [
//                            'dissociate' => Expected::once(true)
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
//            $this->service->dissociate($query, $org)
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
//            UserQuery::class
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationUsers' => Expected::once(
//                    $this->make(
//                        OrganizationUsers::class,
//                        [
//                            'associate' => Expected::once(true)
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
//            $this->service->associate($query, $org)
//        );
//    }
//
//    /**
//     * @expectedException \Exception
//     */
//    public function testRecordNotFoundException()
//    {
//
//        $method = new \ReflectionMethod(
//            $this->service,
//            'recordNotFoundException'
//        );
//        $method->setAccessible(true);
//
//        $method->invoke($this->service);
//    }
}