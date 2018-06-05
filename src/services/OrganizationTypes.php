<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\models\FieldLayout;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\services\traits\records\AccessorByString;
use flipbox\organizations\db\OrganizationTypeQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use flipbox\organizations\Organizations as OrganizationPlugin;
use flipbox\organizations\records\OrganizationType;
use flipbox\organizations\records\OrganizationType as TypeRecord;
use flipbox\organizations\records\OrganizationTypeAssociation;
use yii\base\Component;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationTypeQuery getQuery($config = []): ActiveQuery
 * @method TypeRecord create(array $attributes = [])
 * @method TypeRecord find($identifier)
 * @method TypeRecord get($identifier)
 * @method TypeRecord findByString($identifier)
 * @method TypeRecord getByString($identifier)
 * @method TypeRecord findByCondition($condition = [])
 * @method TypeRecord getByCondition($condition = [])
 * @method TypeRecord findByCriteria($criteria = [])
 * @method TypeRecord getByCriteria($criteria = [])
 * @method TypeRecord[] findAllByCondition($condition = [])
 * @method TypeRecord[] getAllByCondition($condition = [])
 * @method TypeRecord[] findAllByCriteria($criteria = [])
 * @method TypeRecord[] getAllByCriteria($criteria = [])
 */
class OrganizationTypes extends Component
{
    use AccessorByString;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = OrganizationPlugin::getInstance()->getSettings();
        $this->cacheDuration = $settings->organizationTypesCacheDuration;
        $this->cacheDependency = $settings->organizationTypesCacheDependency;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return TypeRecord::class;
    }

    /**
     * @return string
     */
    protected function stringProperty(): string
    {
        return 'handle';
    }

    /*******************************************
     * RESOLVE
     *******************************************/

    /**
     * @param mixed $type
     * @return TypeRecord
     */
    public function resolve($type): TypeRecord
    {
        if ($type = $this->find($type)) {
            return $type;
        }

        $type = ArrayHelper::toArray($type, [], false);

        try {
            $object = $this->create($type);
        } catch (\Exception $e) {
            $object = new TypeRecord();
            ObjectHelper::populate(
                $object,
                $type
            );
        }

        return $object;
    }


    /**
     * @param TypeRecord|null $type
     * @return TypeRecord|null
     */
    public static function resolveFromRequest(TypeRecord $type = null)
    {
        if ($identifier = Craft::$app->getRequest()->getParam('type')) {
            return OrganizationPlugin::getInstance()->getOrganizationTypes()->get($identifier);
        }

        if ($type instanceof TypeRecord) {
            return $type;
        }

        return null;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function prepareQueryConfig($config = [])
    {
        $config['with'] = ['siteSettingRecords'];
        return $config;
    }

    /*******************************************
     * BEFORE/AFTER SAVE
     *******************************************/

    /**
     * @param TypeRecord $type
     * @return bool
     * @throws Exception
     */
    public function beforeSave(TypeRecord $type): bool
    {
        $fieldLayout = $type->getFieldLayout();

        $this->handleOldFieldLayout($type, $fieldLayout);

        if ($fieldLayout === null || $fieldLayout->id == $this->getDefaultFieldLayoutId()) {
            return true;
        }

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            return false;
        }

        return true;
    }

    /**
     * @param TypeRecord $type
     * @param FieldLayout|null $fieldLayout
     */
    private function handleOldFieldLayout(TypeRecord $type, FieldLayout $fieldLayout = null)
    {
        $oldFieldLayoutId = (int)$type->getOldAttribute('fieldLayoutId');

        if ($oldFieldLayoutId !== null &&
            $oldFieldLayoutId != $fieldLayout->id &&
            $oldFieldLayoutId != $this->getDefaultFieldLayoutId()
        ) {
            Craft::$app->getFields()->deleteLayoutById($oldFieldLayoutId);
        }
    }

    /**
     * @return int
     */
    private function getDefaultFieldLayoutId(): int
    {
        return (int)OrganizationPlugin::getInstance()->getSettings()->getFieldLayout()->id;
    }

    /**
     * @param TypeRecord $type
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterSave(TypeRecord $type)
    {
        if (!OrganizationPlugin::getInstance()->getOrganizationTypeSettings()->saveByType($type)) {
            throw new Exception("Unable to save site settings");
        };
    }

    /**
     * @param OrganizationTypeQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function saveAssociations(
        OrganizationTypeQuery $query,
        OrganizationElement $organization
    ): bool {
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $associationService = OrganizationPlugin::getInstance()->getOrganizationTypeAssociations();

        $query = $associationService->getQuery([
            $associationService::SOURCE_ATTRIBUTE => $organization->getId() ?: false
        ]);

        $query->setCachedResult(
            $this->toAssociations($models, $organization->getId())
        );

        return $associationService->save($query);
    }

    /**
     * @param OrganizationTypeQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function dissociate(
        OrganizationTypeQuery $query,
        OrganizationElement $organization
    ): bool {
        return $this->associations(
            $query,
            $organization,
            [
                OrganizationPlugin::getInstance()->getOrganizationTypeAssociations(),
                'dissociate'
            ]
        );
    }

    /**
     * @param OrganizationTypeQuery $query
     * @param OrganizationElement $organization
     * @return bool
     * @throws \Exception
     */
    public function associate(
        OrganizationTypeQuery $query,
        OrganizationElement $organization
    ): bool {
        return $this->associations(
            $query,
            $organization,
            [
                OrganizationPlugin::getInstance()->getOrganizationTypeAssociations(),
                'associate'
            ]
        );
    }

    /**
     * @param OrganizationTypeQuery $query
     * @param OrganizationElement $organization
     * @param callable $callable
     * @return bool
     */
    protected function associations(OrganizationTypeQuery $query, OrganizationElement $organization, callable $callable)
    {
        if (null === ($models = $query->getCachedResult())) {
            return true;
        }

        $models = ArrayHelper::index($models, 'id');

        $success = true;
        $ids = [];
        $count = count($models);
        $i = 0;
        foreach ($this->toAssociations($models, $organization->getId()) as $association) {
            if (true === call_user_func_array($callable, [$association, ++$i === $count])) {
                ArrayHelper::remove($models, $association->typeId);
                $ids[] = $association->typeId;
                continue;
            }

            $success = false;
        }

        $query->organizationTypeId($ids);

        if ($success === false) {
            $query->setCachedResult($models);
        }

        return $success;
    }

    /**
     * @param OrganizationType[] $types
     * @param int $organizationId
     * @return OrganizationTypeAssociation[]
     */
    private function toAssociations(
        array $types,
        int $organizationId
    ) {
        $associations = [];
        $sortOrder = 1;
        foreach ($types as $type) {
            $associations[] = new OrganizationTypeAssociation([
                'organizationId' => $organizationId,
                'typeId' => $type->id,
                'sortOrder' => $sortOrder++
            ]);
        }

        return $associations;
    }
}
