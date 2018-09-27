<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\Organizations as OrganizationsPlugin;
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
        $this->service = (new OrganizationsPlugin('users'))
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
    public function testSaveAssociationsWithNoCachedResult()
    {
        $query = $this->make(
            OrganizationQuery::class,
            [
                'getCachedResult' => Expected::once()
            ]
        );

        $user = $this->make(
            User::class
        );

        $this->assertTrue(
            $this->service->saveAssociations($query, $user)
        );
    }
//
//    /**
//     * @throws \Exception
//     */
//    public function testSaveAssociationsWithCachedResult()
//    {
//        $user = $this->make(
//            User::class,
//            [
//                'id' => 1
//            ]
//        );
//
//        $query = $this->make(
//            OrganizationQuery::class,
//            [
//                'getCachedResult' => Expected::once([$user]),
//                'all' => Expected::once([$user]),
//                'setCachedResult' => Expected::once()
//            ]
//        );
//
//        $service = $this->make(
//            $this->service,
//            [
//                'toAssociations' => Expected::once([
//                    new UserAssociation()
//                ])
//            ]
//        );
//
//        $this->assertTrue(
//            $service->saveAssociations($query, $user)
//        );
//    }
}
