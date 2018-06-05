<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\cp\controllers\settings\view;

use Craft;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\organizations\records\OrganizationType;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class OrganizationTypesController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = parent::TEMPLATE_BASE . '/organizationTypes';

    /**
     * The insert/update view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @return \flipbox\organizations\services\OrganizationTypes
     */
    protected function typeService()
    {
        return $this->module->module->getOrganizationTypes();
    }

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['types'] = $this->typeService()->findAll();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * Insert/Update
     *
     * @param string|int|null $identifier
     * @param OrganizationType $type
     * @return Response
     */
    public function actionUpsert($identifier = null, OrganizationType $type = null)
    {
        if (null === $type) {
            if (null === $identifier) {
                $type = $this->typeService()->create();
            } else {
                $type = $this->typeService()->get($identifier);
            }
        }

        $variables = [];
        if ($type->getIsNewRecord()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $type);
        }

        $variables['type'] = $type;
        $variables['fullPageForm'] = true;
        $variables['tabs'] = $this->getTabs();

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /*******************************************
     * TABS
     *******************************************/

    /**
     * @return array|null
     */
    protected function getTabs(): array
    {
        return [
            'type' => [
                'label' => Craft::t('organizations', 'Type'),
                'url' => '#type'
            ],
            'layout' => [
                'label' => Craft::t('organizations', 'Layout'),
                'url' => '#layout'
            ]
        ];
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/organization-types';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/organization-types';
    }

    /*******************************************
     * INSERT VARIABLES
     *******************************************/

    /**
     * @param array $variables
     */
    protected function insertVariables(array &$variables)
    {
        parent::insertVariables($variables);
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/{id}');
    }

    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param OrganizationType $type
     */
    protected function updateVariables(array &$variables, OrganizationType $type)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Craft::t('organizations', 'Edit') . ' ' . $type->name;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $type->getId());
        $variables['crumbs'][] = [
            'label' => $type->name,
            'url' => UrlHelper::url($variables['continueEditingUrl'])
        ];
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);
        $variables['title'] .= ': Types';
        $variables['crumbs'][] = [
            'label' => Craft::t('organizations', 'Types'),
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
