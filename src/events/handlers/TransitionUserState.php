<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\organizations\events\handlers;

use craft\elements\User;
use craft\events\ModelEvent;
use flipbox\organizations\Organizations;
use flipbox\organizations\records\UserAssociation;
use yii\helpers\Json;
use yii\web\UserEvent;

/**
 * This event will transition a user from 'invited' to another ('active' by default) upon login.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class TransitionUserState
{
    /**
     * The state to transition to.
     *
     * @var string
     */
    public static $state = UserAssociation::STATE_ACTIVE;

    /**
     * @param ModelEvent $event
     */
    public static function handle(UserEvent $event)
    {
        /** @var User $user */
        $user = $event->identity;

        try {
            static::transitionStates($user);
        } catch (\Exception $e) {
            Organizations::warning(
                sprintf(
                    "Exception caught while trying to transition user '%s' organization type states. Exception: [%s].",
                    $user->getId(),
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );
        }
    }

    /**
     * @param User $user
     */
    protected static function transitionStates(User $user)
    {
        $userAssociations = $user->getOrganizations()
            ->getRelationships()
            ->where('state', UserAssociation::STATE_INVITED);

        /** @var UserAssociation $association */
        foreach ($userAssociations as $association) {
            static::transitionState($association);
        }
    }

    /**
     * @param UserAssociation $association
     */
    protected static function transitionState(UserAssociation $association)
    {
        if ($association->state == static::$state) {
            return;
        }

        $state = $association->state;
        $association->state = static::$state;

        Organizations::info(
            sprintf(
                "Transitioning user '%s' associated to organization '%s' from state '%s' to '%s'.",
                $association->getUserId(),
                $association->getOrganizationId(),
                $state,
                $association->state
            ),
            __METHOD__
        );

        try {
            if (!$association->save(true)) {
                Organizations::warning(
                    sprintf(
                        "Failed to transition user '%s' associated to organization '%s' from state '%s' to '%s'. Exception: [%s].",
                        $association->getUserId(),
                        $association->getOrganizationId(),
                        $state,
                        $association->state,
                        (string)Json::encode($association->getErrors())
                    ),
                    __METHOD__
                );
            };
        } catch (\Exception $e) {
            Organizations::warning(
                sprintf(
                    "Exception caught while trying to transition user '%s' associated to organization '%s' from state '%s' to '%s'. Exception: [%s].",
                    $association->getUserId(),
                    $association->getOrganizationId(),
                    $state,
                    $association->state,
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );
        }
    }
}
