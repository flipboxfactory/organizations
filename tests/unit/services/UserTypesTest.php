<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\db\Query;
use craft\elements\User;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\records\UserType;
use flipbox\organizations\records\UserTypeAssociation;
use flipbox\organizations\services\UserTypeAssociations;
use flipbox\organizations\services\UserTypes;

class UserTypesTest_z extends Unit
{
//    /**
//     * @var UserTypes
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
//            ->getUserTypes();
//    }
//
//    /**
//     *
//     */
//    public function testRecordClass()
//    {
//        $this->assertEquals(
//            UserType::class,
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
//        /** @var UserTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(
//                    $this->make(
//                        UserType::class
//                    )
//                ),
//            ]
//        );
//
//        $this->assertInstanceOf(
//            UserType::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testResolve()
//    {
//        /** @var UserTypes $service */
//        $service = $this->make(
//            $this->service,
//            [
//                'find' => Expected::once(),
//                'create' => Expected::once(
//                    $this->make(
//                        UserType::class
//                    )
//                )
//            ]
//        );
//
//        $this->assertInstanceOf(
//            UserType::class,
//            $service->resolve('foo')
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociate()
//    {
//        $type = $this->make(
//            UserType::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $user = $this->make(
//            User::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $org = $this->make(
//            Organization::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $service = $this->make(
//            $this->service,
//            [
//                'associationId' => Expected::once(1)
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getUserTypeAssociations' => Expected::once(
//                    $this->make(
//                        UserTypeAssociations::class,
//                        [
//                            'associate' => Expected::once(true),
//                            'create' => Expected::once(
//                                $this->make(
//                                    UserTypeAssociation::class
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
//        $this->assertTrue(
//            $service->associate(
//                $type,
//                $user,
//                $org
//            )
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testDissociate()
//    {
//        $type = $this->make(
//            UserType::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $user = $this->make(
//            User::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $org = $this->make(
//            Organization::class,
//            [
//                'getId' => Expected::once(1)
//            ]
//        );
//
//        $service = $this->make(
//            $this->service,
//            [
//                'associationId' => Expected::once(1)
//            ]
//        );
//
//        $plugin = $this->make(
//            Organizations::class,
//            [
//                'getUserTypeAssociations' => Expected::once(
//                    $this->make(
//                        UserTypeAssociations::class,
//                        [
//                            'dissociate' => Expected::once(true),
//                            'create' => Expected::once(
//                                $this->make(
//                                    UserTypeAssociation::class
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
//        $this->assertTrue(
//            $service->dissociate(
//                $type,
//                $user,
//                $org
//            )
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationIdQuery()
//    {
//        $userId = 1;
//        $orgId = 1;
//
//        $method = new \ReflectionMethod(
//            $this->service,
//            'associationIdQuery'
//        );
//        $method->setAccessible(true);
//
//        $query = $method->invoke($this->service, $userId, $orgId);
//
//        $this->assertEquals(
//            [
//                'organizationId' => $orgId,
//                'userId' => $userId,
//            ],
//            $query->where
//        );
//        $this->assertEquals(
//            [
//                'organizationId' => $orgId,
//                'userId' => $userId,
//            ],
//            $query->where
//        );
//
//        $this->assertEquals(
//            [
//                'id'
//            ],
//            $query->select
//        );
//
//        $this->assertEquals(
//            [
//                UserAssociation::tableName()
//            ],
//            $query->from
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationId()
//    {
//        $id = 1;
//        $service = $this->make(
//            $this->service,
//            [
//                'associationIdQuery' => Expected::once(
//                    $this->make(
//                        Query::class,
//                        [
//                            'scalar' => Expected::once($id)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        $method = new \ReflectionMethod(
//            $service,
//            'associationId'
//        );
//        $method->setAccessible(true);
//
//        $this->assertEquals(
//            $id,
//            $method->invoke($service, 1, 1)
//        );
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testAssociationIdEmpty()
//    {
//        $id = false;
//        $service = $this->make(
//            $this->service,
//            [
//                'associationIdQuery' => Expected::once(
//                    $this->make(
//                        Query::class,
//                        [
//                            'scalar' => Expected::once($id)
//                        ]
//                    )
//                )
//            ]
//        );
//
//        $method = new \ReflectionMethod(
//            $service,
//            'associationId'
//        );
//        $method->setAccessible(true);
//
//        $this->assertNull(
//            $method->invoke($service, 1, 1)
//        );
//    }
}
