/** global: Craft */
/** global: Garnish */
/**
 * User Category editor
 */
Craft.OrganizationUserCategoryEditor = Craft.HUDEditor.extend({
    init: function (element, settings) {
        this.base(element, $.extend({}, Craft.OrganizationUserCategoryEditor.defaults, settings));
    }
}, {
    defaults: {
        getHtmlAction: 'organizations/cp/user-categories/get-editor-html',
        saveAction: 'organizations/cp/user-categories/save-associations'
    }
});
