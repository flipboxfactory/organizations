<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\models\FieldLayout;
use craft\services\Fields;
use flipbox\organizations\models\Settings;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\services\OrganizationTypes;
use flipbox\organizations\services\OrganizationTypeSettings;
use yii\base\InvalidConfigException;

class OrganizationTypesTest extends Unit
{
    /**
     * @var OrganizationTypes
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getOrganizationTypes();
    }

    /**
     *
     */
    public function testRecordClass()
    {
        $this->assertEquals(
            OrganizationType::class,
            $this->service::recordClass()
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testStringProperty()
    {
        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'stringProperty'
        );
        $method->setAccessible(true);

        $this->assertEquals(
            'handle',
            $method->invoke($this->service)
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolveFound()
    {
        /** @var OrganizationTypes $service */
        $service = $this->make(
            $this->service,
            [
                'find' => Expected::once(
                    $this->make(
                        OrganizationType::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            OrganizationType::class,
            $service->resolve('foo')
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolve()
    {
        /** @var OrganizationTypes $service */
        $service = $this->make(
            $this->service,
            [
                'find' => Expected::once(),
                'create' => Expected::once(
                    $this->make(
                        OrganizationType::class
                    )
                )
            ]
        );

        $this->assertInstanceOf(
            OrganizationType::class,
            $service->resolve('foo')
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testPrepareQueryConfig()
    {
        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'prepareQueryConfig'
        );
        $method->setAccessible(true);

        $this->assertArraySubset(
            ['with' => ['siteSettingRecords']],
            $method->invoke($this->service)
        );
    }

    /**
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function testBeforeSaveSuccess()
    {
        /** @var OrganizationType $type */
        $type = $this->make(
            OrganizationType::class,
            [
                'getFieldLayout' => $this->make(
                    FieldLayout::class,
                    [

                    ]
                )
            ]
        );

        /** @var OrganizationTypes $service */
        $service = $this->make(
            $this->service,
            [
                'getDefaultFieldLayoutId' => Expected::once(1),
            ]
        );

        \Craft::$app->set(
            'fields',
            $this->make(
                Fields::class,
                [
                    'saveLayout' => Expected::once(true)
                ]
            )
        );

        $this->assertTrue(
            $service->beforeSave($type)
        );
    }

    /**
     * @throws \Exception
     */
    public function testBeforeSaveMatch()
    {
        /** @var OrganizationType $type */
        $type = $this->make(
            OrganizationType::class,
            [
                'getFieldLayout' => $this->make(
                    FieldLayout::class,
                    [
                        'id' => 1
                    ]
                )
            ]
        );

        /** @var OrganizationTypes $service */
        $service = $this->make(
            $this->service,
            [
                'getDefaultFieldLayoutId' => Expected::exactly(2, 1),
            ]
        );

        $this->assertTrue(
            $service->beforeSave($type)
        );
    }

    /**
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function testBeforeSaveFail()
    {
        /** @var OrganizationType $type */
        $type = $this->make(
            OrganizationType::class,
            [
                'getFieldLayout' => $this->make(
                    FieldLayout::class,
                    [

                    ]
                )
            ]
        );

        /** @var OrganizationTypes $service */
        $service = $this->make(
            $this->service,
            [
                'getDefaultFieldLayoutId' => Expected::once(1),
            ]
        );

        \Craft::$app->set(
            'fields',
            $this->make(
                Fields::class,
                [
                    'saveLayout' => Expected::once(false)
                ]
            )
        );

        $this->assertFalse(
            $service->beforeSave($type)
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetDefaultFieldLayoutId()
    {
        $id = 1;

        $plugin = $this->make(
            Organizations::class,
            [
                'getSettings' => Expected::once(
                    $this->make(
                        Settings::class,
                        [
                            'getFieldLayout' => Expected::once(
                                $this->make(
                                    FieldLayout::class,
                                    [
                                        'id' => $id
                                    ]
                                )
                            )
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'getDefaultFieldLayoutId'
        );
        $method->setAccessible(true);

        $this->assertEquals(
            $id,
            $method->invoke($this->service)
        );
    }

    /**
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function testAfterSaveSuccess()
    {
        /** @var OrganizationType $type */
        $type = $this->make(
            OrganizationType::class,
            [
                'getFieldLayout' => $this->make(
                    FieldLayout::class,
                    [

                    ]
                )
            ]
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getOrganizationTypeSettings' => Expected::once(
                    $this->make(
                        OrganizationTypeSettings::class,
                        [
                            'saveByType' => Expected::once(true)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->service->afterSave($type);
    }

    /**
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     * @expectedException \Exception
     */
    public function testAfterSaveFail()
    {
        /** @var OrganizationType $type */
        $type = $this->make(
            OrganizationType::class,
            [
                'getFieldLayout' => $this->make(
                    FieldLayout::class,
                    [

                    ]
                )
            ]
        );

        $plugin = $this->make(
            Organizations::class,
            [
                'getOrganizationTypeSettings' => Expected::once(
                    $this->make(
                        OrganizationTypeSettings::class,
                        [
                            'saveByType' => Expected::once(false)
                        ]
                    )
                )
            ]
        );

        \Craft::$app->loadedModules[Organizations::class] = $plugin;
        \Yii::$app->loadedModules[Organizations::class] = $plugin;

        $this->service->afterSave($type);
    }
}
