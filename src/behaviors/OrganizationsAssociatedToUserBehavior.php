<?php

namespace flipbox\organizations\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\relationships\RelationshipInterface;
use flipbox\organizations\relationships\OrganizationRelationship;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\validators\OrganizationsValidator;
use Tightenco\Collect\Support\Collection;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Exception;

/**
 * Class UserOrganizationsBehavior
 * @package flipbox\organizations\behaviors
 *
 * @property User $owner;
 */
class OrganizationsAssociatedToUserBehavior extends Behavior
{
    /**
     * @var RelationshipInterface
     */
    private $manager;

    /**
     * Whether associated organizations should be saved
     *
     * @var bool
     */
    private $saveOrganizations = true;

    /**
     * @return static
     */
    public function withOrganizations(): self
    {
        $this->saveOrganizations = true;
        return $this;
    }

    /**
     * @return static
     */
    public function withoutUsers(): self
    {
        $this->saveOrganizations = false;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Validate organizations
        Event::on(
            User::class,
            User::EVENT_AFTER_VALIDATE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterValidate($user);
            }
        );

        // Associate
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function (ModelEvent $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterSave($user);
            }
        );

        // Dissociate
        Event::on(
            User::class,
            User::EVENT_AFTER_DELETE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->onAfterDelete($user);
            }
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     */
    private function onAfterDelete(User $user)
    {
        /** @var static $user */
        $user->getOrganizations()
            ->clear()
            ->save();
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function onAfterSave(User $user)
    {
        // Check cache for explicitly set (and possibly not saved) organizations
        if (true === $this->saveOrganizations && $user->getOrganizations()->isMutated()) {

            /** @var Organization $organization */
            foreach ($user->getOrganizations()->getCollection() as $organization) {
                if (null === $organization->getId()) {
                    if (!Craft::$app->getElements()->saveElement($organization)) {
                        $user->addError(
                            'organizations',
                            OrganizationPlugin::t('Unable to save organization.')
                        );

                        throw new Exception('Unable to save organization.');
                    }
                }
            }

            $user->getOrganizations()->save();
        }
    }

    /**
     * @param User|self $user
     * @return void
     */
    private function onAfterValidate(User $user)
    {
        (new OrganizationsValidator())->validateAttribute($user, 'organizations');
    }

    /**
     * Get an array of associated organizations
     *
     * @return OrganizationRelationship|RelationshipInterface
     */
    public function getOrganizations(): RelationshipInterface
    {
        if (null === $this->manager) {
            $this->manager = new OrganizationRelationship($this->owner);
        }

        return $this->manager;
    }

    /**
     * Set an array or query of organizations to a user
     *
     * @param $organizations
     * @return $this
     */
    public function setOrganizations($organizations)
    {
        $this->getOrganizations()->clear()->add($organizations);
        return $this;
    }
}
