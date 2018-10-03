<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\db\UserAssociationQuery;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation as OrganizationUserAssociation;
use flipbox\organizations\services\UserOrganizationAssociations;

class UserOrganizationsTest extends Unit
{

    /**
     * @var \flipbox\organizations\services\UserOrganizations
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getUserOrganizations();
    }

    /**
     * @throws \Exception
     */
    public function testSaveAssociationsWithNoCachedResult()
    {
        $user = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class,
            [
                'getCachedResult' => Expected::once()
            ]
        );

        $this->assertTrue(
            $this->service->saveAssociations($user, $query)
        );
    }

    /**
     * @throws \Exception
     */
    public function testSaveAssociationsWithCachedResult()
    {
        $id = 1;

        $user = $this->make(
            User::class,
            [
                'id' => $id,
                'getId' => Expected::exactly(2, $id)
            ]
        );

        $query = $this->make(
            OrganizationQuery::class,
            [
                'userId' => $id,
                'getCachedResult' => Expected::once([$user])
            ]
        );

        $service = $this->make(
            $this->service,
            [
                'toAssociations' => Expected::once([
                    $this->make(OrganizationUserAssociation::class)
                ])
            ]
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getUserOrganizationAssociations' => Expected::once(
                    $this->make(
                        UserOrganizationAssociations::class,
                        [
                            'getQuery' => Expected::once(
                                $this->make(
                                    UserAssociationQuery::class
                                )
                            ),
                            'save' => Expected::once(true)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->assertTrue(
            $service->saveAssociations($user, $query)
        );
    }

    /**
     * @throws \Exception
     */
    public function testDissociate()
    {
        $user = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $service = $this->make($this->service, [
            'associations' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->dissociate($user, $query)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociate()
    {
        $user = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $service = $this->make($this->service, [
            'associations' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->associate($user, $query)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociationsWithEmptyCache()
    {
        $user = $this->make(
            User::class
        );

        $query = $this->make(
            OrganizationQuery::class
        );

        $assService = $this->make(
            UserOrganizationAssociations::class
        );

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'associations'
        );
        $method->setAccessible(true);

        $this->assertTrue(
            $method->invoke(
                $this->service,
                $user,
                $query,
                [
                    $assService,
                    'associate'
                ]
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociationsWithCache()
    {
        $user = $this->make(
            User::class,
            [
                'getId' => Expected::once(1)
            ]
        );

        $query = $this->make(
            OrganizationQuery::class,
            [
                'getCachedResult' => Expected::once([$user]),
                'id' => Expected::once()
            ]
        );

        $assService = $this->make(
            UserOrganizationAssociations::class,
            [
                'associate' => Expected::once(true)
            ]
        );

        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
            ->setMethods(['attributes'])
            ->getMock();

        $record->method('attributes')->willReturn([
            'userId',
            'organizationId'
        ]);

        $service = $this->make(
            $this->service,
            [
                'toAssociations' => Expected::once([$record])
            ]
        );

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'associations'
        );
        $method->setAccessible(true);

        $this->assertTrue(
            $method->invoke(
                $service,
                $user,
                $query,
                [
                    $assService,
                    'associate'
                ]
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociationsWithCacheNoAssociations()
    {
        $user = $this->make(
            User::class,
            [
                'getId' => Expected::once(1)
            ]
        );

        $query = $this->make(
            OrganizationQuery::class,
            [
                'getCachedResult' => Expected::once([$user]),
                'setCachedResult' => Expected::once(),
                'id' => Expected::once()
            ]
        );

        $assService = $this->make(
            UserOrganizationAssociations::class,
            [
                'associate' => Expected::once(false)
            ]
        );

        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
            ->setMethods(['attributes'])
            ->getMock();

        $record->method('attributes')->willReturn([
            'id'
        ]);

        $service = $this->make(
            $this->service,
            [
                'toAssociations' => Expected::once([$record])
            ]
        );

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'associations'
        );
        $method->setAccessible(true);

        $this->assertFalse(
            $method->invoke(
                $service,
                $user,
                $query,
                [
                    $assService,
                    'associate'
                ]
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testToAssociations()
    {
        $id = 1;

        $record = $this->getMockBuilder(OrganizationUserAssociation::class)
            ->setMethods(['attributes'])
            ->getMock();

        $record->method('attributes')->willReturn([
            'id',
            'userId',
            'userOrder'
        ]);

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'toAssociations'
        );
        $method->setAccessible(true);

        $plugin = $this->make(
            Organizations::class,
            [
                'getUserOrganizationAssociations' => Expected::once(
                    $this->make(
                        UserOrganizationAssociations::class,
                        [
                            'create' => Expected::once(
                                $record
                            )
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $method->invoke(
            $this->service,
            [
                $record
            ],
            $id
        );
    }
}