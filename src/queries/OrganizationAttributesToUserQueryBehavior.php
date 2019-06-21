<?php

namespace flipbox\organizations\queries;

use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\organizations\elements\Organization;
use yii\base\Behavior;

/**
 * Class UserOrganizationBehavior
 * @package flipbox\organizations\queries
 *
 * @property UserQuery $owner
 */
class OrganizationAttributesToUserQueryBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->handler = new UserQueryParamHandler($this);
    }

    /**
     * @var UserQueryParamHandler
     */
    private $handler;

    /**
     * @param UserQuery $query
     * @throws \craft\db\QueryAbortedException
     */
    public function applyOrganizationParams(UserQuery $query)
    {
        $this->handler->applyParams($query);
    }

    /**
     * @return UserQueryParamHandler
     */
    public function getOrganization(): UserQueryParamHandler
    {
        return $this->handler;
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return UserQuery
     */
    public function setOrganization($value): UserQuery
    {
        if (is_array($value)) {
            $this->findSubNodes($value);

            // If we removed everything, we're all done here
            if (empty($value)) {
                return $this->owner;
            }
        }

        $this->handler->setOrganization($value);
        return $this->owner;
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return UserQuery
     */
    public function organization($value): UserQuery
    {
        return $this->setOrganization($value);
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return UserQuery
     */
    public function setOrganizationId($value): UserQuery
    {
        return $this->setOrganization($value);
    }

    /**
     * @param string|string[]|int|int[]|Organization|Organization[]|null $value
     * @return UserQuery
     */
    public function organizationId($value): UserQuery
    {
        return $this->setOrganization($value);
    }

    /**
     * Extract the sub nodes (userType and type) from a criteria array
     *
     * @param array $value
     */
    private function findSubNodes(array &$value)
    {
        if (null !== ($subValue = ArrayHelper::remove($value, 'organizationType'))) {
            $this->handler->setOrganizationType($subValue);
        }

        if (null !== ($subValue = ArrayHelper::remove($value, 'userType'))) {
            $this->handler->setUserType($subValue);
        }

        if (null !== ($subValue = ArrayHelper::remove($value, 'userState'))) {
            $this->handler->setUserState($subValue);
        }
    }
}
