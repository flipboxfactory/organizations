/** global: Craft */
/** global: Garnish */
/**
 * User Type editor
 */
Craft.OrganizationUserTypeEditor = Craft.HUDEditor.extend({
    init: function (element, settings) {
        this.base(element, $.extend({}, Craft.OrganizationUserTypeEditor.defaults, settings));
    }
}, {
    defaults: {
        getHtmlAction: 'organizations/cp/user-types/get-editor-html',
        saveAction: 'organizations/cp/user-types/save-associations'
    }
});