<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\db\UserQuery;
use flipbox\organizations\db\UserAssociationQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation as OrganizationUserAssociation;
use flipbox\organizations\services\OrganizationUserAssociations;

class OrganizationUsersTest extends Unit
{

//    /**
//     * @var \flipbox\organizations\services\OrganizationUsers
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
//            ->getOrganizationUsers();
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveAssociationsWithNoCachedResult()
//    {
//        $query = $this->make(
//            UserQuery::class,
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
//            $this->service->saveAssociations($org, $query)
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
//            UserQuery::class,
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
//                    $this->make(OrganizationUserAssociation::class)
//                ])
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationUserAssociations' => Expected::once(
//                    $this->make(
//                        OrganizationUserAssociations::class,
//                        [
//                            'getQuery' => Expected::once(
//                                $this->make(
//                                    UserAssociationQuery::class
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
//            $service->saveAssociations($org, $query)
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
//        $service = $this->make($this->service, [
//            'associations' => Expected::once(
//                true
//            )
//        ]);
//
//        $this->assertTrue(
//            $service->dissociate($org, $query)
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
//        $service = $this->make($this->service, [
//            'associations' => Expected::once(
//                true
//            )
//        ]);
//
//        $this->assertTrue(
//            $service->associate($org, $query)
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
//            UserQuery::class
//        );
//
//        $assService = $this->make(
//            OrganizationUserAssociations::class
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
//                $org,
//                $query,
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
//            UserQuery::class,
//            [
//                'getCachedResult' => Expected::once([$org]),
//                'id' => Expected::once()
//            ]
//        );
//
//        $assService = $this->make(
//            OrganizationUserAssociations::class,
//            [
//                'associate' => Expected::once(true)
//            ]
//        );
//
//        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'userId',
//            'organizationId'
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
//                $org,
//                $query,
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
//            UserQuery::class,
//            [
//                'getCachedResult' => Expected::once([$org]),
//                'setCachedResult' => Expected::once(),
//                'id' => Expected::once()
//            ]
//        );
//
//        $assService = $this->make(
//            OrganizationUserAssociations::class,
//            [
//                'associate' => Expected::once(false)
//            ]
//        );
//
//        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'id'
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
//                $org,
//                $query,
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
//        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
//            ->setMethods(['attributes'])
//            ->getMock();
//
//        $record->method('attributes')->willReturn([
//            'id',
//            'userId',
//            'organizationOrder'
//        ]);
//
//        // Protected
//        $method = new \ReflectionMethod(
//            $this->service,
//            'toAssociations'
//        );
//        $method->setAccessible(true);
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getOrganizationUserAssociations' => Expected::once(
//                    $this->make(
//                        OrganizationUserAssociations::class,
//                        [
//                            'create' => Expected::once(
//                                $record
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
//            [
//                $record
//            ],
//            $id
//        );
//    }
}