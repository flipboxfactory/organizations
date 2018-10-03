<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\services\UserOrganizations;
use flipbox\organizations\services\Users;

class UsersTest extends Unit
{
    /**
     * @var Users
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getUsers();
    }

    /**
     * @inheritdoc
     */
    public function testElementClass()
    {
        $this->assertEquals(
            User::class,
            $this->service::elementClass()
        );
    }

    /**
     * @inheritdoc
     */
    public function testIdentifierCondition()
    {
        $service = $this->service;

        // Protected
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('identifierCondition');
        $method->setAccessible(true);

        $email = 'foo@bar.com';
        $id = 1;

        $this->assertEquals(
            [
                'status' => null,
                'where' => [
                    'or',
                    ['username' => $email],
                    ['email' => $email]
                ]
            ],
            $method->invoke($service, $email)
        );

        $this->assertEquals(
            [
                'status' => null,
                'id' => $id
            ],
            $method->invoke($service, $id)
        );

        $this->assertEquals(
            [
                'status' => null,
                'id' => $id
            ],
            $method->invoke($service, ['id' => $id])
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolveFound()
    {
        $service = $this->make(
            $this->service,
            [
                'find' => Expected::once(
                    $this->make(
                        User::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $service->resolve('foo')
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolveArray()
    {
        $service = $this->make(
            $this->service,
            [
                'find' => Expected::once(
                    $this->make(
                        User::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $service->resolve(['id' => 1])
        );
    }

    /**
     * @throws \Exception
     */
    public function testFind()
    {
        $service = $this->make(
            $this->service,
            [
                'parentFind' => Expected::once(
                    $this->make(
                        User::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $service->find(1)
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindCurrentUser()
    {
        \Craft::$app->set('user', $this->make(
            \craft\web\User::class,
            [
                'getIdentity' => Expected::once($this->make(
                    User::class
                ))
            ]
        ));

        $this->assertInstanceOf(
            User::class,
            $this->service->find()
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolveCreate()
    {
        $service = $this->make(
            $this->service,
            [
                'find' => Expected::once(),
                'create' => Expected::once(
                    $this->make(
                        User::class
                    )
                )
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $service->resolve('foo')
        );
    }

    /**
     * @throws \Exception
     */
    public function testSaveAssociations()
    {
        $user = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getUserOrganizations' => Expected::once(
                    $this->make(
                        UserOrganizations::class,
                        [
                            'saveAssociations' => Expected::once(true)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->assertTrue(
            $this->service->saveAssociations($query, $user)
        );
    }

    /**
     * @throws \Exception
     */
    public function testDissociate()
    {
        $users = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getUserOrganizations' => Expected::once(
                    $this->make(
                        UserOrganizations::class,
                        [
                            'dissociate' => Expected::once(true)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->assertTrue(
            $this->service->dissociate($query, $users)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociate()
    {
        $users = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getUserOrganizations' => Expected::once(
                    $this->make(
                        UserOrganizations::class,
                        [
                            'associate' => Expected::once(true)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->assertTrue(
            $this->service->associate($query, $users)
        );
    }
}
