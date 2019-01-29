<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\organizations\objects\OrganizationMutatorTrait;
use yii\base\InvalidArgumentException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DissociateUsersFromOrganizationAction extends ElementAction
{
    use OrganizationMutatorTrait;

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
        return Craft::t('organizations', 'Disassociate');
    }

    /**
     * @inheritdoc
     * @param ElementQuery $query
     * @throws \Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        if (null === ($organization = $this->getOrganization())) {
            throw new InvalidArgumentException("Organization could not be found");
        }

        if (!$query instanceof UserQuery) {
            throw new InvalidArgumentException(sprintf(
                "Query must be an instance of %s, %s given.",
                UserQuery::class,
                get_class($query)
            ));
        }

        // Get the count because it's cleared when dissociated
        $userCount = $query->count();

        if (false === $organization->dissociateUsers($query)) {
            $this->setMessage(
                Craft::t(
                    'organizations',
                    $this->assembleFailMessage($query)
                )
            );

            return false;
        }

        $this->setMessage($this->assembleSuccessMessage($userCount));
        return true;
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleFailMessage(ElementQueryInterface $query): string
    {
        $message = 'Failed to remove user: ';

        $users = $query->all();
        $badEmails = ArrayHelper::index($users, 'email');

        $message .= implode(", ", $badEmails);

        return Craft::t('organizations', $message);
    }

    /**
     * @param int $count
     * @return string
     */
    private function assembleSuccessMessage(int $count): string
    {
        $message = 'User';

        if ($count != 1) {
            $message = '{count} ' . $message . 's';
        }

        $message .= ' dissociated.';

        return Craft::t('organizations', $message, ['count' => $count]);
    }
}
