<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\organizations\events\ChangeStateEvent;
use flipbox\organizations\records\Organization as OrganizationRecord;
use yii\base\Component;
use flipbox\organizations\Organizations as OrganizationPlugin;

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
     * @event ChangeStateEvent The event that is triggered before a organization has a custom status change.
     *
     * You may set [[ChangeStateEvent::isValid]] to `false` to prevent the organization changing the status.
     */
    const EVENT_BEFORE_STATE_CHANGE = 'beforeStateChange';

    /**
     * @event ChangeStateEvent The event that is triggered after a organization has a custom status change.
     *
     * You may set [[ChangeStateEvent::isValid]] to `false` to prevent the organization changing the status.
     */
    const EVENT_AFTER_STATE_CHANGE = 'afterStateChange';

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
        // Change state w/ events (see below)
        $changedState = $this->hasStateChanged($record);

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$record->save()) {
                $transaction->rollBack();
                return false;
            }

            if (false !== $changedState &&
                true !== $this->changeState($record, $changedState)
            ) {
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

    /**
     * @param OrganizationRecord $record
     * @return false|string|null
     */
    private function hasStateChanged(OrganizationRecord $record)
    {
        if ($record->getIsNewRecord()) {
            return false;
        }

        if (!$record->isAttributeChanged('state', false)) {
            return false;
        }

        $oldState = $record->getOldAttribute('state');
        $newState = $record->getAttribute('state');

        // Revert record to old
        $record->setAttribute('state', $oldState);

        return $newState;
    }


    /**
     * @param OrganizationRecord $record
     * @param string|null $state
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function changeState(
        OrganizationRecord $record,
        string $state = null
    ): bool {
        $event = new ChangeStateEvent([
            'organization' => $record,
            'to' => $state
        ]);

        $this->trigger(
            static::EVENT_BEFORE_STATE_CHANGE,
            $event
        );

        if (!$event->isValid) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            $record->state = $event->to;

            // Organization Record (status only)
            if (!$record->save(true, ['state'])) {
                $transaction->rollBack();
                return false;
            }

            // Trigger event
            $this->trigger(
                static::EVENT_AFTER_STATE_CHANGE,
                $event
            );
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
        return true;
    }
}
