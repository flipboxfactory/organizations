<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use flipbox\organizations\db\UserOrganizationAssociationQuery;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\services\UserOrganizationAssociations;

class UserOrganizationAssociationsTest extends Unit
{
    /**
     * @var UserOrganizationAssociations
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getUserOrganizationAssociations();
    }

    /**
     * @throws \ReflectionException
     */
    public function testTableAlias()
    {
        $service = $this->service;

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'tableAlias'
        );
        $method->setAccessible(true);

        $result = $method->invoke($service);

        $this->assertEquals(
            UserAssociation::tableAlias(),
            $result
        );
    }

    /**
     * @inheritdoc
     */
    public function testRecordClass()
    {
        $this->assertEquals(
            UserAssociation::class,
            $this->service::recordClass()
        );
    }

    /**
     * @inheritdoc
     */
    public function testGetQuery()
    {
        $query = $this->service->getQuery();

        $this->assertInstanceOf(
            UserOrganizationAssociationQuery::class,
            $query
        );
    }

    /**
     * @inheritdoc
     */
    public function testGetQueryWithConfig()
    {
        // Make sure the configuration is applied
        $query = $this->service->getQuery([
            'indexBy' => 'organizationId',
            'userId' => [1, 2, 3],
            'where' => [
                'or',
                [
                    'dateCreated' => ':empty:'
                ]
            ],
            'foo' => 'bar' // This is not valid and should fail silently
        ]);

        $this->assertEquals(
            $query->indexBy,
            'organizationId'
        );

        $this->assertEquals(
            $query->userId,
            [1, 2, 3]
        );

        $this->assertEquals(
            $query->where,
            [
                'or',
                [
                    'dateCreated' => ':empty:'
                ]
            ]
        );
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testAssociationQuery()
    {
        // Mock Query
        $query = $this->make(UserOrganizationAssociationQuery::class);

        // Mock Record
        $record = $this->makeEmpty(UserAssociation::class, [
            'getOrganizationId' => Expected::once()
        ]);

        // Mock Service
        $service = $this->make(UserOrganizationAssociations::class, [
            'getQuery' => Expected::once($query)
        ]);

        // Protected
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('associationQuery');
        $method->setAccessible(true);

        $result = $method->invoke($service, $record);

        $this->assertInstanceOf(
            UserOrganizationAssociationQuery::class,
            $result
        );
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testExistingAssociations()
    {
        // Mock Record
        $record = $this->makeEmpty(UserAssociation::class);

        // Mock Query
        $query = $this->make(UserOrganizationAssociationQuery::class, [
            'all' => Expected::once([$record])
        ]);

        // Mock Service
        $service = $this->make(UserOrganizationAssociations::class, [
            'resolveStringAttribute' => Expected::once('foo'),
            'getQuery' => Expected::once($query)
        ]);

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'existingAssociations'
        );
        $method->setAccessible(true);


        $result = $method->invoke($service, $query);
        $this->assertTrue(is_array($result));

        // Mock Service
        $service = $this->make(UserOrganizationAssociations::class, [
            'resolveStringAttribute' => Expected::once()
        ]);

        $result = $method->invoke($service, $query);
        $this->assertEquals($result, []);
    }

    /**
     * @throws \ReflectionException
     */
    public function testQuery()
    {
        // Protected
        $ref = new \ReflectionClass($this->service);
        $method = $ref->getMethod('query');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 1);

        $this->assertInstanceOf(
            UserOrganizationAssociationQuery::class,
            $result
        );

        $this->assertEquals(
            $result->orderBy,
            [UserOrganizationAssociations::SORT_ORDER_ATTRIBUTE => SORT_ASC]
        );
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testAssociations()
    {
        // Mock Record
        $record = $this->makeEmpty(UserAssociation::class);

        // Mock Query
        $query = $this->make(UserOrganizationAssociationQuery::class, [
            'all' => Expected::once([$record])
        ]);

        // Mock Service
        $service = $this->make(UserOrganizationAssociations::class, [
            'getQuery' => Expected::once($query)
        ]);

        // Protected
        $method = new \ReflectionMethod(
            $service,
            'associations'
        );
        $method->setAccessible(true);

        $result = $method->invoke($service, 1);

        $this->assertTrue(
            is_array($result)
        );
    }
}
