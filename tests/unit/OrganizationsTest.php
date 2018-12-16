<?php

namespace flipbox\organizations\tests;

use Codeception\Test\Unit;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\services\UserOrganizationAssociations;
use flipbox\organizations\services\Users;
use flipbox\organizations\services\UserTypeAssociations;

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
//
//    /**
//     * Test the component is set correctly
//     */
//    public function testOrganizationUserAssociationsComponent()
//    {
//        $this->assertInstanceOf(
//            OrganizationUserAssociations::class,
//            $this->module->getOrganizationUserAssociations()
//        );
//
//        $this->assertInstanceOf(
//            OrganizationUserAssociations::class,
//            $this->module->organizationUserAssociations
//        );
//    }

//    /**
//     * Test the component is set correctly
//     */
//    public function testUserOrganizationsComponent()
//    {
//        $this->assertInstanceOf(
//            UserOrganizations::class,
//            $this->module->getUserOrganizations()
//        );
//
//        $this->assertInstanceOf(
//            UserOrganizations::class,
//            $this->module->userOrganizations
//        );
//    }
//
//    /**
//     * Test the component is set correctly
//     */
//    public function testUserOrganizationAssociationsComponent()
//    {
//        $this->assertInstanceOf(
//            UserOrganizationAssociations::class,
//            $this->module->getUserOrganizationAssociations()
//        );
//
//        $this->assertInstanceOf(
//            UserOrganizationAssociations::class,
//            $this->module->userOrganizationAssociations
//        );
//    }
//
//    /**
//     * Test the component is set correctly
//     */
//    public function testUsersComponent()
//    {
//        $this->assertInstanceOf(
//            Users::class,
//            $this->module->getUsers()
//        );
//
//        $this->assertInstanceOf(
//            Users::class,
//            $this->module->users
//        );
//    }

//    /**
//     * Test the component is set correctly
//     */
//    public function testUserTypeAssociationsComponent()
//    {
//        $this->assertInstanceOf(
//            UserTypeAssociations::class,
//            $this->module->getUserTypeAssociations()
//        );
//
//        $this->assertInstanceOf(
//            UserTypeAssociations::class,
//            $this->module->userTypeAssociations
//        );
//    }

}
