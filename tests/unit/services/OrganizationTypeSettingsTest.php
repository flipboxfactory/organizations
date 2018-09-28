<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use craft\elements\db\UserQuery;
use craft\helpers\DateTimeHelper;
use craft\i18n\PhpMessageSource;
use flipbox\organizations\db\OrganizationTypeAssociationQuery;
use flipbox\organizations\db\OrganizationTypeQuery;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\models\Settings;
use flipbox\organizations\models\SiteSettings;
use flipbox\organizations\Organizations;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\services\Element;
use flipbox\organizations\services\OrganizationTypes;
use flipbox\organizations\services\OrganizationTypeSettings;
use flipbox\organizations\records\OrganizationTypeSiteSettings;
use flipbox\organizations\services\Records;
use yii\base\Exception;
use yii\db\ActiveQuery;

class OrganizationTypeSettingsTest extends Unit
{

    /**
     * @var OrganizationTypeSettings
     */
    private $service;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->service = (new OrganizationsPlugin('organizations'))
            ->getOrganizationTypeSettings();
    }

    /**
     *
     */
    public function testRecordClass()
    {
        $this->assertEquals(
            OrganizationTypeSiteSettings::class,
            $this->service::recordClass()
        );
    }

    /**
     *
     */
    public function testSaveByTypeSuccess()
    {
        $settings = $this->getMockBuilder(OrganizationTypeSiteSettings::class)
            ->setMethods(['attributes', 'save', 'delete'])
            ->getMock();

        $settings->method('attributes')->willReturn([
            'siteId', 'typeId'
        ]);

        $settings->siteId = 1;
        $settings->typeId = 1;

        $settings->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $settings->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $query = $this->getMockBuilder(ActiveQuery::class)
            ->setConstructorArgs([
                OrganizationType::class
            ])
            ->setMethods(['indexBy', 'all'])
            ->getMock();

        $query->expects($this->once())
            ->method('indexBy')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('all')
            ->willReturn([2 => $settings]);

        $record = $this->getMockBuilder(OrganizationType::class)
            ->setMethods(['getSiteSettings', 'hasMany', 'getId'])
            ->getMock();

        $record->expects($this->once())
            ->method('getSiteSettings')
            ->willReturn([1 => $settings]);

        $record->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $record->expects($this->once())
            ->method('hasMany')
            ->with(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
            ->willReturn($query);

        $service = $this->make(
            $this->service,
            [
                'reSaveOrganizations' => Expected::once(true)
            ]
        );

        $this->assertTrue(
            $service->saveByType($record)
        );
    }

    /**
     *
     */
    public function testSaveByTypeFail()
    {
        $settings = $this->getMockBuilder(OrganizationTypeSiteSettings::class)
            ->setMethods(['attributes', 'save', 'delete', 'getFirstErrors'])
            ->getMock();

        $settings->method('attributes')->willReturn([
            'siteId', 'typeId'
        ]);

        $settings->siteId = 1;
        $settings->typeId = 1;

        $settings->expects($this->once())
            ->method('delete')
            ->willReturn(false);

        $settings->expects($this->once())
            ->method('getFirstErrors')
            ->willReturn([
                'foo' => 'bar'
            ]);

        $settings->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $query = $this->getMockBuilder(ActiveQuery::class)
            ->setConstructorArgs([
                OrganizationType::class
            ])
            ->setMethods(['indexBy', 'all'])
            ->getMock();

        $query->expects($this->once())
            ->method('indexBy')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('all')
            ->willReturn([2 => $settings]);

        $record = $this->getMockBuilder(OrganizationType::class)
            ->setMethods(['getSiteSettings', 'hasMany', 'getId'])
            ->getMock();

        $record->expects($this->once())
            ->method('getSiteSettings')
            ->willReturn([1 => $settings]);

        $record->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $record->expects($this->once())
            ->method('hasMany')
            ->with(OrganizationTypeSiteSettings::class, ['typeId' => 'id'])
            ->willReturn($query);

        $service = $this->make(
            $this->service,
            [
                'reSaveOrganizations' => Expected::once(true)
            ]
        );

        $this->assertFalse(
            $service->saveByType($record)
        );
    }

//    /**
//     * @param TypeModel $type
//     * @return bool
//     * @throws \Exception
//     * @throws \Throwable
//     * @throws \yii\db\StaleObjectException
//     */
//    public function saveByType(
//        TypeModel $type
//    ): bool {
//        $successful = true;
//
//        /** @var TypeSettingsRecord[] $allSettings */
//        $allSettings = $type->hasMany(TypeSettingsRecord::class, ['typeId' => 'id'])
//            ->indexBy('siteId')
//            ->all();
//
//        foreach ($type->getSiteSettings() as $model) {
//            ArrayHelper::remove($allSettings, $model->siteId);
//            $model->typeId = $type->getId();
//
//            if (!$model->save()) {
//                $successful = false;
//                // Log the errors
//                $error = Craft::t(
//                    'organizations',
//                    "Couldn't save site settings due to validation errors:"
//                );
//                foreach ($model->getFirstErrors() as $attributeError) {
//                    $error .= "\n- " . Craft::t('organizations', $attributeError);
//                }
//
//                $type->addError('sites', $error);
//            }
//        }
//
//        // Delete old settings records
//        foreach ($allSettings as $settings) {
//            $settings->delete();
//            $this->reSaveOrganizations($settings);
//        }
//
//        return $successful;
//    }
//
//    /**
//     * @param TypeSettingsRecord $type
//     * @param bool $insert
//     * @param array $changedAttributes
//     */
//    public function afterSave(TypeSettingsRecord $type, bool $insert, array $changedAttributes)
//    {
//        if ($insert === true) {
//            return;
//        }
//
//        if (array_key_exists('uriFormat', $changedAttributes) ||
//            (array_key_exists('hasUrls', $changedAttributes) &&
//                $type->getOldAttribute('hasUrls') != $type->getAttribute('hasUrls'))
//        ) {
//            $this->reSaveOrganizations($type);
//        }
//    }
//
//    /**
//     * @param TypeSettingsRecord $type
//     */
//    private function reSaveOrganizations(TypeSettingsRecord $type)
//    {
//        Craft::$app->getQueue()->push(new ResaveElements([
//            'description' => Craft::t('organizations', 'Re-saving organizations (Site: {site})', [
//                'site' => $type->getSiteId(),
//            ]),
//            'elementType' => OrganizationElement::class,
//            'criteria' => [
//                'siteId' => $type->getSiteId(),
//                'typeId' => $type->getTypeId(),
//                'status' => null,
//                'enabledForSite' => false,
//            ]
//        ]));
//    }


}