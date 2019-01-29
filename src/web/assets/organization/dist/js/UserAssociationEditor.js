/** global: Craft */
/** global: Garnish */
/**
 * Element editor
 */
Craft.UserAssociationEditor = Craft.BaseElementEditor.extend(
    {
        // Additional settings
        init: function (elementType, settings) {
            this.base(elementType, $.extend({}, Craft.UserAssociationEditor.defaults, settings));
        },

        // replacing hard coded action url w/ this.settings.loadHudAction
        loadHud: function () {
            this.onBeginLoading();
            var data = this.getBaseData();
            data.includeSites = this.settings.showSiteSwitcher;
            Craft.postActionRequest(this.settings.loadHudAction, data, $.proxy(this, 'showHud'));
        },

        // replacing hard coded action url w/ this.settings.loadHudAction
        reloadForm: function (data, callback) {
            data = $.extend(this.getBaseData(), data);

            Craft.postActionRequest(this.settings.loadHudAction, data, $.proxy(function (response, textStatus) {
                if (textStatus === 'success') {
                    this.updateForm(response);
                }

                if (callback) {
                    callback(textStatus);
                }
            }, this));
        },

        // replacing hard coded action url w/ this.settings.saveAction
        saveElement: function () {
            var validators = this.settings.validators;

            if ($.isArray(validators)) {
                for (var i = 0; i < validators.length; i++) {
                    if ($.isFunction(validators[i]) && !validators[i].call()) {
                        return false;
                    }
                }
            }

            this.$spinner.removeClass('hidden');

            var data = $.param(this.getBaseData()) + '&' + this.hud.$body.serialize();
            Craft.postActionRequest(this.settings.saveAction, data, $.proxy(function (response, textStatus) {
                this.$spinner.addClass('hidden');

                if (textStatus === 'success') {
                    if (response.success) {
                        if (this.$element && this.siteId == this.$element.data('site-id')) {
                            // Update the label
                            var $title = this.$element.find('.title'),
                                $a = $title.find('a');

                            if ($a.length && response.cpEditUrl) {
                                $a.attr('href', response.cpEditUrl);
                                $a.text(response.newTitle);
                            } else {
                                $title.text(response.newTitle);
                            }
                        }

                        this.closeHud();
                        this.onSaveElement(response);
                    } else {
                        this.updateForm(response);
                        Garnish.shake(this.hud.$hud);
                    }
                }
            }, this));
        },

        onHideHud: function () {
            if (this.reloadIndex && this.settings.elementIndex) {
                this.settings.elementIndex.updateElements();
            }

            this.base();
        }
    },
    {
        defaults: {
            loadHudAction: 'organizations/cp/users/association-editor-html',
            saveAction: 'organizations/cp/users/save-association',
        }
    }
);
