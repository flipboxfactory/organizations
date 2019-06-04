<?php

namespace flipbox\organizations\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\organizations\elements\Organization;
use flipbox\organizations\objects\OrganizationsAssociatedToUserManager;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\queries\OrganizationQuery;
use flipbox\organizations\validators\OrganizationsValidator;
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
     * @var OrganizationsAssociatedToUserManager
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
     * @return OrganizationsAssociatedToUserManager
     */
    public function getOrganizationManager(): OrganizationsAssociatedToUserManager
    {
        if (null === $this->manager) {
            $this->manager = new OrganizationsAssociatedToUserManager($this->owner);
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
     * @return Organization[]
     */
    public function getOrganizations(): array
    {
        return ArrayHelper::getColumn(
            $this->getOrganizationManager()->findAll(),
            'organization'
        );
    }

    /**
     * Set an array or query of organizations to a user
     *
     * @param $organizations
     * @return $this
     *
     * @deprecated
     */
    public function setOrganizations($organizations)
    {
        $this->getOrganizationManager()->setMany($organizations);
        return $this;
    }

    /**
     * Add an array of organizations to a user.  Note: This does not save the organization associations.
     *
     * @param $organizations
     * @return $this
     *
     * @deprecated
     */
    protected function addOrganizations(array $organizations)
    {
        $this->getOrganizationManager()->addMany($organizations);
        return $this;
    }

    /**
     * Add a organization to a user.  Note: This does not save the organization association.
     *
     * @param Organization $organization
     * @param bool $addToOrganization
     * @return $this
     *
     * @deprecated
     */
    public function addOrganization(Organization $organization, bool $addToOrganization = true)
    {
        $this->getOrganizationManager()->addOne($organization);

        // Add user to organization as well?
        if ($addToOrganization && $organization->getId() !== null) {
            $organization->addUser($this->owner);
        }

        return $this;
    }

    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @deprecated
     */
    public function saveOrganizations(): bool
    {
        return $this->getOrganizationManager()->save();
    }

    /**
     * @param Organization $organization
     * @param int|null $sortOrder
     * @return bool
     *
     * @deprecated
     */
    public function associateOrganization(Organization $organization, int $sortOrder = null): bool
    {
        return $this->getOrganizationManager()->associateOne($organization, $sortOrder);
    }

    /**
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Throwable
     *
     * @deprecated
     */
    public function associateOrganizations(OrganizationQuery $query): bool
    {
        return $this->getOrganizationManager()->associateMany($query);
    }

    /**
     * @param Organization $organization
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @deprecated
     */
    public function dissociateOrganization(Organization $organization): bool
    {
        return $this->getOrganizationManager()->dissociateOne($organization);
    }

    /**
     * @param OrganizationQuery $query
     * @return bool
     * @throws \Throwable
     *
     * @deprecated
     */
    public function dissociateOrganizations(OrganizationQuery $query): bool
    {
        return $this->getOrganizationManager()->dissociateMany($query);
    }

    /**
     * @return User
     *
     * @deprecated
     */
    public function resetOrganizations()
    {
        $this->getOrganizationManager()->reset();
        return $this->owner;
    }
}
