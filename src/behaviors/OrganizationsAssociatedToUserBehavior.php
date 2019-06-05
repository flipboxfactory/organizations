<?php

namespace flipbox\organizations\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\managers\RelationshipManagerInterface;
use flipbox\organizations\managers\OrganizationRelationshipManager;
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
     * @var RelationshipManagerInterface
     */
    private $manager;

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
     * @return RelationshipManagerInterface
     */
    public function getOrganizationManager(): RelationshipManagerInterface
    {
        if (null === $this->manager) {
            $this->manager = new OrganizationRelationshipManager($this->owner);
        }

        return $this->manager;
    }

    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     */
    private function onAfterDelete(User $user)
    {
        /** @var static $user */
        $user->getOrganizationManager()
            ->setMany([])
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
        if ($user->getOrganizationManager()->isMutated()) {

            /** @var Organization $organization */
            foreach ($user->getOrganizations() as $organization) {
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
        }

        $user->getOrganizationManager()->save();
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
     * @param array $criteria
     * @return OrganizationQuery
     */
    public function organizationQuery($criteria = []): OrganizationQuery
    {
        $query = Organization::find()
            ->user($this->owner)
            ->orderBy([
                'organizationOrder' => SORT_ASC
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * Get an array of associated organizations
     *
     * @param array $criteria
     * @return Organization[]|Collection
     */
    public function getOrganizations(array $criteria = []): Collection
    {
        return Collection::make(QueryHelper::configure(
            $this->organizationQuery($criteria)
                ->id(
                    $this->getOrganizationManager()->findAll()
                        ->pluck('organizationId')
                )
                ->fixedOrder(true)
                ->limit(null),
            $criteria
        )->all());
    }

    /**
     * Set an array or query of organizations to a user
     *
     * @param $organizations
     * @return $this
     */
    public function setOrganizations($organizations)
    {
        $this->getOrganizationManager()->setMany($organizations);
        return $this;
    }
}
