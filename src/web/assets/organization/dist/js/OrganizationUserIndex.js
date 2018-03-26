Craft.OrganizationUserIndex = Craft.NestedElementIndex.extend({
    getViewClass: function (mode) {
        if (mode === 'table') {
            return Craft.TableOrganizationUserIndexView; // User our custom view
        }
        return this.base(mode);
    }
});