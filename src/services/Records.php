<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\Organization as OrganizationRecord;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationRecord create(array $attributes = [])
 * @method OrganizationRecord find($identifier)
 * @method OrganizationRecord get($identifier)
 * @method OrganizationRecord findByCondition($condition = [])
 * @method OrganizationRecord getByCondition($condition = [])
 * @method OrganizationRecord findByCriteria($criteria = [])
 * @method OrganizationRecord getByCriteria($criteria = [])
 * @method OrganizationRecord[] findAllByCondition($condition = [])
 * @method OrganizationRecord[] getAllByCondition($condition = [])
 * @method OrganizationRecord[] findAllByCriteria($criteria = [])
 * @method OrganizationRecord[] getAllByCriteria($criteria = [])
 */
class Records extends Component
{
    use Accessor;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->recordsCacheDuration;
        $this->cacheDependency = $settings->recordsCacheDependency;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return OrganizationRecord::class;
    }

    /**
     * @param OrganizationRecord $record
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function save(OrganizationRecord $record): bool
    {
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$record->save()) {
                $transaction->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
        return true;
    }
}
