/** global: Craft */
/** global: Garnish */
/**
 * User Type editor
 */

Craft.TableOrganizationUserIndexView = Craft.TableElementIndexView.extend({
    afterInit: function () {
        this.base();

        // Append our action to rows
        this.appendActionToRows(this.getAllElements());
    },

    initTableHeaders: function () {
        this.base();

        var $tr = $('<th />')
            .addClass('thin');

        this.$table.find('thead tr').append($tr);
    },


    appendElements: function ($newElements) {
        this.base($newElements);
        this.appendActionToRows($newElements);
    },

    appendActionToRows: function (rows) {
        for (var i = 0; i < rows.length; i++) {
            this.appendActionToRow(rows.eq(i));
        }
    },

    appendActionToRow: function (row) {
        var $action = $('<span />')
            .addClass('settings icon manage-types hud-editor-toggle')
            .attr('title', 'Manage Types')
            .attr('role', 'button');

        var $td = $('<td />').append($action);

        row.append($td);

        this.addListener($action, 'click', this.handleActionColumnClick);

        if ($.isTouchCapable()) {
            this.addListener($action, 'taphold', this.handleActionColumnClick);
        }
    },

    handleActionColumnClick: function (ev) {
        this.createTypeEditor($(ev.target));
    },

    createTypeEditor: function ($type) {
        var params = $.extend({}, this.elementIndex.settings.viewParams);
        params.user = $type.parents('tr').data('id');

        return new Craft.OrganizationUserTypeEditor($type, {
            params: params,
            onSaveElement: $.proxy(function (response) {
                console.log(response);
            }, this)
        });
    }
});