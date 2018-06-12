<?php

namespace flipbox\organizations\tests\models;

use Codeception\Test\Unit;
use flipbox\organizations\models\Settings;

class SettingsTest extends Unit
{
    /**
     * Set a state as a string and result in an array
     */
    public function testStateCanBeSetAsString()
    {
        $settings = new Settings();

        $settings->setStates('foo');
        $this->assertEquals(
            $settings->getStates(),
            ['foo']
        );
    }

    /**
     * Set a state as an array and result in an array
     */
    public function testStateCanBeSetAsArray()
    {
        $settings = new Settings();

        $settings->setStates(['foo', 'bar']);
        $this->assertEquals(
            $settings->getStates(),
            ['foo', 'bar']
        );
    }

    /**
     * Set a state as an array and result in an array
     */
    public function testHasState()
    {
        $settings = new Settings();

        $this->assertFalse(
            $settings->hasStates()
        );

        $settings->setStates(['foo', 'bar']);
        $this->assertTrue(
            $settings->hasStates()
        );
    }
}
