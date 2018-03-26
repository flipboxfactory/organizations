<?php

namespace flipbox\organization\elements\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organization\db\OrganizationQuery;
use flipbox\organization\elements\Organization;
use flipbox\organization\Organizations as OrganizationPlugin;
use flipbox\organization\validators\OrganizationsValidator;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Class UserOrganizationsBehavior
 * @package flipbox\organization\elements\behaviors
 *
 * @property User $owner;
 */
class UserOrganizationsBehavior extends Behavior
{
    /**
     * @var OrganizationQuery|null
     */
    private $organizations;

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
                $this->validate($user);
            }
        );

        // Associate
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function (ModelEvent $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->save($user);
            }
        );

        // Dissociate
        Event::on(
            User::class,
            User::EVENT_AFTER_DELETE,
            function (ModelEvent $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->delete($user);
            }
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    private function delete(User $user)
    {
        $this->dissociateOrganizations($user);
    }

    /**
     * @param User|self $user
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function dissociateOrganizations(User $user)
    {
        $associationService = OrganizationPlugin::getInstance()->getUserAssociations();
        foreach ($user->getOrganizations()->all() as $organization) {
            $associationService->dissociate(
                $associationService->create([
                    'userId' => $user->getId(),
                    'organizationId' => $organization->getId()
                ])
            );
        }
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function save(User $user)
    {
        $this->saveOrganizations($user);
        $this->associateOrganizations($user);
    }

    /**
     * @param User|self $user
     * @throws \Exception
     */
    private function associateOrganizations(User $user)
    {
        $associationService = OrganizationPlugin::getInstance()->getUserAssociations();
        foreach ($user->getOrganizations()->all() as $organization) {
            $associationService->associate(
                $associationService->create([
                    'userId' => $user->getId(),
                    'organizationId' => $organization->getId()
                ])
            );
        }
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function saveOrganizations(User $user)
    {
        // Check cache for explicitly set (and possibly not saved) organizations
        if (null !== ($organizations = $user->getOrganizations()->getCachedResult())) {

            /** @var Organization $organization */
            foreach ($organizations as $organization) {
                if (!$organization->id) {
                    if (!Craft::$app->getElements()->saveElement($organization)) {
                        $user->addError(
                            'organizations',
                            Craft::t('organizations', 'Unable to save organization.')
                        );

                        throw new Exception('Unable to save organization.');
                    }
                }
            }
        }
    }


    /**
     * @param User|self $user
     * @return void
     */
    private function validate(User $user)
    {
        $error = null;

        if (!(new OrganizationsValidator())->validate($user->getOrganizations(), $error)) {
            $user->addError('organizations', $error);
        }
    }


    /**
     * @return OrganizationQuery
     */
    private function createQuery(): OrganizationQuery
    {
        return OrganizationPlugin::getInstance()->getOrganizations()->getQuery([
            'user' => $this->owner
        ]);
    }

    /**
     * Get a query with associated organizations
     *
     * @param array $criteria
     * @return OrganizationQuery
     */
    public function getOrganizations($criteria = []): OrganizationQuery
    {
        if (null === $this->organizations) {
            $this->organizations = $this->createQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->organizations,
                $criteria
            );
        }

        return $this->organizations;
    }

    /**
     * Associate users to an organization
     *
     * @param $organizations
     * @return $this
     */
    public function setOrganizations($organizations)
    {
        if ($organizations instanceof OrganizationQuery) {
            $this->organizations = $organizations;
            return $this;
        }

        // Reset the query
        $this->organizations = $this->createQuery();
        $this->organizations->setCachedResult([]);
        $this->addOrganizations($organizations);
        return $this;
    }

    /**
     * Associate an array of users to an organization
     *
     * @param $organizations
     * @return $this
     */
    protected function addOrganizations(array $organizations)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($organizations)) {
            $organizations = [$organizations];
        }

        foreach ($organizations as $key => $organization) {
            if (!$organization = OrganizationPlugin::getInstance()->getOrganizations()->resolve($organization)) {
                OrganizationPlugin::info(sprintf(
                    "Unable to resolve organization: %s",
                    (string)Json::encode($organization)
                ));
                continue;
            }

            $this->addOrganization($organization);
        }

        return $this;
    }

    /**
     * Associate a user to an organization
     *
     * @param Organization $organization
     * @param bool $addToOrganization
     * @return $this
     */
    public function addOrganization(Organization $organization, bool $addToOrganization = true)
    {
        // Current associated organizations
        $allOrganizations = $this->getOrganizations()->all();
        $allOrganizations[] = $organization;

        $this->getOrganizations()->setCachedResult($allOrganizations);

        // Add user to organization as well?
        if ($addToOrganization && $organization->id !== null) {
            $user = $this->owner;
            if ($user instanceof User) {
                $organization->addUser($user);
            };
        }

        return $this;
    }
}
