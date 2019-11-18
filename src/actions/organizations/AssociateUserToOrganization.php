<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\actions\organizations;

use Craft;
use flipbox\craft\ember\actions\records\SaveRecordTrait;
use flipbox\organizations\records\UserAssociation;
use yii\base\Action;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.0.0
 */
class AssociateUserToOrganization extends Action
{
    use SaveRecordTrait, LookupAssociationTrait;

    /**
     * @var array
     */
    public $validBodyParams = [
        'sortOrder' => 'userOrder',
        'state'
    ];

    /**
     * @inheritdoc
     * @param UserAssociation $record
     * @return bool
     */
    protected function performAction(UserAssociation $association): bool
    {
        return $association->save();
    }

    /**
     * Body params that should be set on the record.
     *
     * @return array
     */
    protected function validBodyParams(): array
    {
        return $this->validBodyParams;
    }

    /**
     * @param UserAssociation $record
     * @inheritDoc
     */
    protected function populate(UserAssociation $record): ActiveRecord
    {
        $record->setAttributes(
            $this->attributeValuesFromBody()
        );

        $types = Craft::$app->getRequest()->getBodyParam('types', []);
        if (!empty($types)) {
            $record->getTypes()->clear()->add($types);
        }

        return $record;
    }
}
