<?php

namespace flipbox\organizations\tests\services;

use Codeception\Test\Unit;
use flipbox\organizations\db\OrganizationUserAssociationQuery;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation;
use flipbox\organizations\services\OrganizationUserAssociations;

class OrganizationUserAssociationsTest extends Unit
{
    /**
     * @var OrganizationUserAssociations
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getOrganizationUserAssociations();
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
            OrganizationUserAssociationQuery::class,
            $query
        );
    }
}