<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\services;

use craft\helpers\ArrayHelper;
use flipbox\ember\exceptions\RecordNotFoundException;
use flipbox\ember\helpers\SiteHelper;
use flipbox\ember\services\traits\elements\MultiSiteAccessor;
use flipbox\organizations\db\OrganizationQuery;
use flipbox\organizations\elements\Organization as OrganizationElement;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method OrganizationElement create($config = [], string $toScenario = null)
 * @method OrganizationElement find($identifier, int $siteId = null)
 * @method OrganizationElement get($identifier, int $siteId = null)
 * @method OrganizationQuery getQuery($criteria = [])
 */
class Organizations extends Component
{
    use MultiSiteAccessor;

    /**
     * @inheritdoc
     */
    public static function elementClass(): string
    {
        return OrganizationElement::class;
    }

    /**
     * @param $identifier
     * @param int|null $siteId
     * @return array
     */
    protected function identifierCondition($identifier, int $siteId = null): array
    {
        $base = [
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'status' => null
        ];

        if (is_array($identifier)) {
            return array_merge($base, $identifier);
        }

        if (!is_numeric($identifier) && is_string($identifier)) {
            $base['slug'] = $identifier;
        } else {
            $base['id'] = $identifier;
        }

        return $base;
    }

    /**
     * @param mixed $organization
     * @return OrganizationElement
     */
    public function resolve($organization)
    {
        if (is_array($organization) &&
            null !== ($id = ArrayHelper::getValue($organization, 'id'))
        ) {
            return $this->get($id);
        }

        if ($object = $this->find($organization)) {
            return $object;
        }

        return $this->create($organization);
    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws RecordNotFoundException
     */
    protected function recordNotFoundException()
    {
        throw new RecordNotFoundException('Record does not exist.');
    }
}
