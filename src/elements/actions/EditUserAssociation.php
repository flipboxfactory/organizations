<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\organizations\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\helpers\Json;
use flipbox\organizations\objects\OrganizationMutatorTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class EditUserAssociation extends ElementAction
{
    use OrganizationMutatorTrait;

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            [
                'organization'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('organizations', 'Edit');
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        $type = Json::encode(static::class);
        $organizationId = $this->getOrganizationId();

        $js = <<<EOT
(function()
{

    var trigger = new Craft.NestedIndexElementActionTrigger(
        index_nested_index_organization_users,
        {
            type: {$type},
            batch: false,
            validateSelection: function(\$selectedItems)
            {
                return Garnish.hasAttr(\$selectedItems.find('.element'), 'data-editable');
            },
            activate: function(\$selectedItems)
            {
                var \$element = \$selectedItems.find('.element:first');
    
                if (index_nested_index_organization_users.viewMode === 'table') {
                    new Craft.UserAssociationEditor(\$element, {
                        params: {
                            organization: {$organizationId},
                            includeTableAttributesForSource: 'organizations:' + index_nested_index_organization_users.sourceKey
                        },
                        onSaveElement: $.proxy(function(response) {
                            if (response.tableAttributes) {
                                index_nested_index_organization_users.view._updateTableAttributes(\$element, response.tableAttributes);
                            }
                        }, this)
                    });
                } else {
                    new Craft.UserAssociationEditor(\$element);
                }
            }
        }
    );
})();
EOT;

        Craft::$app->getView()->registerJs($js);
    }
}
