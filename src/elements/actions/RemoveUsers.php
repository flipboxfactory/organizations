<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organization\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\organization\elements\Organization;
use flipbox\organization\Organizations as OrganizationPlugin;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RemoveUsers extends ElementAction
{
    /**
     * @var string|int|array|Organization
     */
    public $organization;

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            [
                'organization'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return 'Remove';
    }

    /**
     * @inheritdoc
     * @param UserQuery $query
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        if (empty($this->organization)) {
            throw new Exception("Organization does not exist with the identifier '{$this->organization}'");
        }

        $organization = OrganizationPlugin::getInstance()->getOrganizations()->get($this->organization);

        // Prep for dissociation
        $query->setCachedResult(
            $query->all()
        );

        if (false === OrganizationPlugin::getInstance()->getUsers()->dissociate(
            $query,
            $organization
        )) {
            $this->setMessage(
                Craft::t(
                    'organizations',
                    $this->assembleFailMessage($query)
                )
            );

            return false;
        }

        $this->setMessage($this->assembleSuccessMessage($query));
        return true;
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleFailMessage(ElementQueryInterface $query): string
    {
        $message = 'Failed to remove user: ';

        $users = $query->getCachedResult();
        $badEmails = ArrayHelper::index($users, 'email');

        $message .= implode(", ", $badEmails);

        return Craft::t('organizations', $message);
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleSuccessMessage(ElementQueryInterface $query): string
    {
        $message = 'User';

        if ($query->count() != 1) {
            $message = $query->count() . ' ' . $message . 's';
        }

        $message .= ' removed.';

        return Craft::t('organizations', $message);
    }
}
