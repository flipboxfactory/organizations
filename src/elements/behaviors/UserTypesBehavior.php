<?php

namespace flipbox\organizations\elements\behaviors;

use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\ember\helpers\QueryHelper;
use flipbox\organizations\db\UserTypeQuery;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\UserType;
use yii\base\Behavior;

/**
 * @property User $owner;
 */
class UserTypesBehavior extends Behavior
{
    /**
     * @var UserTypeQuery|null
     */
    private $userTypes;

    /**
     * @return UserTypeQuery
     */
    private function createQuery(): UserTypeQuery
    {
        return UserType::findOne([
            'user' => $this->owner
        ]);
    }

    /**
     * Get a query with associated types
     *
     * @param array $criteria
     * @return UserTypeQuery
     */
    public function getUserTypes($criteria = []): UserTypeQuery
    {
        if (null === $this->userTypes) {
            $this->userTypes = $this->createQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->userTypes,
                $criteria
            );
        }

        return $this->userTypes;
    }

    /**
     * Associate users to an type
     *
     * @param $userTypes
     * @return $this
     */
    public function setUserTypes($userTypes)
    {
        if ($userTypes instanceof UserTypeQuery) {
            $this->userTypes = $userTypes;
            return $this;
        }

        // Reset the query
        $this->userTypes = $this->createQuery();
        $this->userTypes->setCachedResult([]);
        $this->addUserTypes($userTypes);
        return $this;
    }

    /**
     * Associate an array of users to an type
     *
     * @param $types
     * @return $this
     */
    protected function addUserTypes(array $types)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($types)) {
            $types = [$types];
        }

        foreach ($types as $key => $type) {
            if (!$type = $this->resolveUserType($type)) {
                OrganizationPlugin::warning(sprintf(
                    "Unable to resolve user type: %s",
                    (string)Json::encode($type)
                ));
                continue;
            }

            $this->addUserType($type);
        }

        return $this;
    }

    /**
     * @param mixed $type
     * @return UserType
     */
    public function resolveUserType($type): UserType
    {
        if (null !== ($type = UserType::findOne($type))) {
            return $type;
        }

        if (!is_array($type)) {
            $type = ArrayHelper::toArray($type, [], false);
        }

        return new UserType($type);
    }


    /**
     * Associate a user to an type
     *
     * @param UserType $type
     * @return $this
     */
    public function addUserType(UserType $type)
    {
        // Current associated types
        $allTypes = $this->getUserTypes()->all();
        $allTypes[] = $type;

        $this->getUserTypes()->setCachedResult($allTypes);

        return $this;
    }
}
