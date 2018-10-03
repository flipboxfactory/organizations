<?php

namespace flipbox\organizations\tests;

use Codeception\Test\Unit;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\services\Element;
use flipbox\organizations\services\Organizations;
use flipbox\organizations\services\OrganizationTypeAssociations;
use flipbox\organizations\services\OrganizationTypes;
use flipbox\organizations\services\OrganizationTypeSettings;
use flipbox\organizations\services\OrganizationUserAssociations;
use flipbox\organizations\services\OrganizationUsers;
use flipbox\organizations\services\Records;
use flipbox\organizations\services\UserOrganizationAssociations;
use flipbox\organizations\services\UserOrganizations;
use flipbox\organizations\services\Users;
use flipbox\organizations\services\UserTypeAssociations;
use flipbox\organizations\services\UserTypes;

class OrganizationsTest extends Unit
{
    /**
     * @var OrganizationsPlugin
     */
    private $module;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new OrganizationsPlugin('organizations');
    }

    /**
     * Test the component is set correctly
     */
    public function testElementComponent()
    {
        $this->assertInstanceOf(
            Element::class,
            $this->module->getElement()
        );

        $this->assertInstanceOf(
            Element::class,
            $this->module->element
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationsComponent()
    {
        $this->assertInstanceOf(
            Organizations::class,
            $this->module->getOrganizations()
        );

        $this->assertInstanceOf(
            Organizations::class,
            $this->module->organizations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationTypeAssociationsComponent()
    {
        $this->assertInstanceOf(
            OrganizationTypeAssociations::class,
            $this->module->getOrganizationTypeAssociations()
        );

        $this->assertInstanceOf(
            OrganizationTypeAssociations::class,
            $this->module->organizationTypeAssociations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationTypesComponent()
    {
        $this->assertInstanceOf(
            OrganizationTypes::class,
            $this->module->getOrganizationTypes()
        );

        $this->assertInstanceOf(
            OrganizationTypes::class,
            $this->module->organizationTypes
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationTypeSettingsComponent()
    {
        $this->assertInstanceOf(
            OrganizationTypeSettings::class,
            $this->module->getOrganizationTypeSettings()
        );

        $this->assertInstanceOf(
            OrganizationTypeSettings::class,
            $this->module->organizationTypeSettings
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationUserAssociationsComponent()
    {
        $this->assertInstanceOf(
            OrganizationUserAssociations::class,
            $this->module->getOrganizationUserAssociations()
        );

        $this->assertInstanceOf(
            OrganizationUserAssociations::class,
            $this->module->organizationUserAssociations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testOrganizationUsersComponent()
    {
        $this->assertInstanceOf(
            OrganizationUsers::class,
            $this->module->getOrganizationUsers()
        );

        $this->assertInstanceOf(
            OrganizationUsers::class,
            $this->module->organizationUsers
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testRecordsComponent()
    {
        $this->assertInstanceOf(
            Records::class,
            $this->module->getRecords()
        );

        $this->assertInstanceOf(
            Records::class,
            $this->module->records
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testUserOrganizationsComponent()
    {
        $this->assertInstanceOf(
            UserOrganizations::class,
            $this->module->getUserOrganizations()
        );

        $this->assertInstanceOf(
            UserOrganizations::class,
            $this->module->userOrganizations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testUserOrganizationAssociationsComponent()
    {
        $this->assertInstanceOf(
            UserOrganizationAssociations::class,
            $this->module->getUserOrganizationAssociations()
        );

        $this->assertInstanceOf(
            UserOrganizationAssociations::class,
            $this->module->userOrganizationAssociations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testUsersComponent()
    {
        $this->assertInstanceOf(
            Users::class,
            $this->module->getUsers()
        );

        $this->assertInstanceOf(
            Users::class,
            $this->module->users
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testUserTypeAssociationsComponent()
    {
        $this->assertInstanceOf(
            UserTypeAssociations::class,
            $this->module->getUserTypeAssociations()
        );

        $this->assertInstanceOf(
            UserTypeAssociations::class,
            $this->module->userTypeAssociations
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testUserTypesComponent()
    {
        $this->assertInstanceOf(
            UserTypes::class,
            $this->module->getUserTypes()
        );

        $this->assertInstanceOf(
            UserTypes::class,
            $this->module->userTypes
        );
    }
}
