<?php

namespace flipbox\organizations\tests;

use Codeception\Test\Unit;
use flipbox\organizations\cp\Cp;
use flipbox\organizations\Organizations;
use flipbox\saml\sp\Saml;

class OrganizationsTest extends Unit
{
    /**
     * @var Organizations
     */
    private $module;

    /**
     * @inheritDoc
     */
    protected function _before()
    {
        $this->module = new Organizations('organizations');
    }

    /**
     * Test the 'CP' module is set correctly
     */
    public function testCpComponent()
    {
        $this->assertInstanceOf(
            Cp::class,
            $this->module->getCp()
        );

        $this->assertInstanceOf(
            Cp::class,
            $this->module->cp
        );
    }
}
