<?php

namespace flipbox\organizations\tests\services;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use flipbox\organizations\Organizations as OrganizationsPlugin;
use flipbox\organizations\records\Organization;

class RecordsTest_z extends Unit
{
//    /**
//     * @var Records
//     */
//    private $service;
//
//    /**
//     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
//     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
//     */
//    protected function _before()
//    {
//        $this->service = (new OrganizationsPlugin('organizations'))
//            ->getRecords();
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function testRecordClass()
//    {
//        $this->assertEquals(
//            Organization::class,
//            $this->service::recordClass()
//        );
//    }
//
//    public function testSaveSuccess()
//    {
//        $record = $this->make(
//            Organization::class, [
//                'save' => Expected::once(true)
//            ]
//        );
//
//        $this->assertTrue(
//            $this->service->save($record)
//        );
//    }
//
//    public function testSaveFail()
//    {
//        $record = $this->make(
//            Organization::class, [
//                'save' => Expected::once(false)
//            ]
//        );
//
//        $this->assertFalse(
//            $this->service->save($record)
//        );
//    }
//
//    /**
//     * @throws \yii\db\Exception
//     * @expectedException \Exception
//     */
//    public function testSaveException()
//    {
//        $record = $this->getMockBuilder(Organization::class)
//            ->setMethods(['save'])
//            ->getMock();
//
//        $record->expects($this->once())
//            ->method('save')
//            ->will($this->throwException(new \Exception));
//
//        $this->service->save($record);
//    }

//    /**
//     * @param OrganizationRecord $record
//     * @return bool
//     * @throws \Exception
//     * @throws \yii\db\Exception
//     */
//    public function save(OrganizationRecord $record): bool
//    {
//        $transaction = Craft::$app->getDb()->beginTransaction();
//        try {
//            if (!$record->save()) {
//                $transaction->rollBack();
//                return false;
//            }
//        } catch (\Exception $e) {
//            $transaction->rollBack();
//            throw $e;
//        }
//
//        $transaction->commit();
//        return true;
//    }

}
