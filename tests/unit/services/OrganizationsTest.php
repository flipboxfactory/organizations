<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\db\UserQuery;
use craft\helpers\DateTimeHelper;
use craft\i18n\PhpMessageSource;
use craft\models\Site;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\db\OrganizationTypeQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\models\Settings;
use flipbox\organizations\models\SiteSettings;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\UserAssociation as OrganizationUserAssociation;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\services\Element;
use flipbox\organizations\services\OrganizationTypes;
use flipbox\organizations\services\OrganizationUserAssociations;
use flipbox\organizations\services\Records;
use yii\base\Exception;

class OrganizationsTest extends Unit
{

    /**
     * @var \flipbox\organizations\services\Organizations
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getOrganizations();
    }

    /**
     * @inheritdoc
     */
    public function testElementClass()
    {
        $this->assertEquals(
            Organization::class,
            $this->service::elementClass()
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testIdentifierConditionAsId()
    {
        $siteId = 1;
        $status = null;
        $identifier = '1';

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'identifierCondition'
        );
        $method->setAccessible(true);

        \Craft::$app->getSites()->setCurrentSite(
            new Site([
                'id' => $siteId
            ])
        );

        $this->assertArraySubset(
            [
                'siteId' => $siteId,
                'status' => $status,
                'id' => $identifier
            ],
            $method->invoke($this->service, $identifier)
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testIdentifierConditionAsSlug()
    {
        $siteId = 1;
        $status = null;
        $identifier = 'flipbox';

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'identifierCondition'
        );
        $method->setAccessible(true);

        \Craft::$app->getSites()->setCurrentSite(
            new Site([
                'id' => $siteId
            ])
        );

        $this->assertArraySubset(
            [
                'siteId' => $siteId,
                'status' => $status,
                'slug' => $identifier
            ],
            $method->invoke($this->service, $identifier)
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testIdentifierConditionAsArray()
    {
        $siteId = 1;
        $status = null;
        $identifier = [
            'slug' => 'flipbox',
            'status' => Organization::STATUS_ARCHIVED
        ];

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'identifierCondition'
        );
        $method->setAccessible(true);

        \Craft::$app->getSites()->setCurrentSite(
            new Site([
                'id' => $siteId
            ])
        );

        $this->assertArraySubset(
            array_merge(
                [
                    'siteId' => $siteId,
                    'status' => $status
                ],
                $identifier
            ),
            $method->invoke($this->service, $identifier)
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
                        Organization::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            Organization::class,
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
                'get' => Expected::once(
                    $this->make(
                        Organization::class
                    )
                ),
            ]
        );

        $this->assertInstanceOf(
            Organization::class,
            $service->resolve(['id' => 1])
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
                        Organization::class
                    )
                )
            ]
        );

        $this->assertInstanceOf(
            Organization::class,
            $service->resolve('foo')
        );
    }

    /**
     * @throws \Exception
     */
    public function testDissociate()
    {
        $org = $this->make(
            Organization::class
        );

        $query = $this->make(
            UserQuery::class
        );

        $service = $this->make($this->service, [
            'dissociateUsers' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->dissociate($query, $org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testDissociateUsers()
    {
        $org = $this->make(
            Organization::class
        );

        $query = $this->make(
            UserQuery::class
        );

        $service = $this->make($this->service, [
            'userAssociations' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->dissociateUsers($query, $org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociate()
    {
        $org = $this->make(
            Organization::class
        );

        $query = $this->make(
            UserQuery::class
        );

        $service = $this->make($this->service, [
            'associateUsers' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->associate($query, $org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociateUsers()
    {
        $org = $this->make(
            Organization::class
        );

        $query = $this->make(
            UserQuery::class
        );

        $service = $this->make($this->service, [
            'userAssociations' => Expected::once(
                true
            )
        ]);

        $this->assertTrue(
            $service->associateUsers($query, $org)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAssociationsWithEmptyCache()
    {

        $org = $this->make(
            Organization::class
        );

        $query = $this->make(
            UserQuery::class
        );

        $assService = $this->make(
            OrganizationUserAssociations::class
        );

        // Protected
        $method = new \ReflectionMethod(
            $this->service,
            'userAssociations'
        );
        $method->setAccessible(true);

        $this->assertTrue(
            $method->invoke(
                $this->service,
                $query,
                $org,
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
        $org = $this->make(
            Organization::class,
            [
                'getId' => Expected::once(1)
            ]
        );

        $query = $this->make(
            UserQuery::class,
            [
                'getCachedResult' => Expected::once([$org]),
                'id' => Expected::once()
            ]
        );

        $assService = $this->make(
            OrganizationUserAssociations::class,
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
            'userAssociations'
        );
        $method->setAccessible(true);

        $this->assertTrue(
            $method->invoke(
                $service,
                $query,
                $org,
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
        $org = $this->make(
            Organization::class,
            [
                'getId' => Expected::once(1)
            ]
        );

        $query = $this->make(
            UserQuery::class,
            [
                'getCachedResult' => Expected::once([$org]),
                'setCachedResult' => Expected::once(),
                'id' => Expected::once()
            ]
        );

        $assService = $this->make(
            OrganizationUserAssociations::class,
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
            'userAssociations'
        );
        $method->setAccessible(true);

        $this->assertFalse(
            $method->invoke(
                $service,
                $query,
                $org,
                [
                    $assService,
                    'associate'
                ]
            )
        );
    }


}