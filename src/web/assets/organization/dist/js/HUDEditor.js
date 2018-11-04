/** global: Craft */
/** global: Garnish */
/**
 * HUD editor
 */
Craft.HUDEditor = Garnish.Base.extend(
    {
        $element: null,

        $form: null,
        $fieldsContainer: null,
        $cancelBtn: null,
        $saveBtn: null,
        $spinner: null,

        hud: null,

        init: function (element, settings) {
            this.$element = $(element);
            this.setSettings(settings, Craft.HUDEditor.defaults);
            this.loadHud();
        },

        getBaseData: function () {
            var data = $.extend({}, this.settings.params);

            if (this.settings.attributes) {
                data.attributes = this.settings.attributes;
            }

            return data;
        },

        loadHud: function () {
            this.onBeginLoading();
            var data = this.getBaseData();
            Craft.postActionRequest(this.settings.getHtmlAction, data, $.proxy(this, 'showHud'));
        },

        showHud: function (response, textStatus) {
            this.onEndLoading();

            if (textStatus === 'success') {
                var $hudContents = $();

                this.$form = $('<div/>');
                this.$fieldsContainer = $('<div class="fields"/>').appendTo(this.$form);

                this.updateForm(response);

                this.onCreateForm(this.$form);

                var $footer = $('<div class="hud-footer"/>').appendTo(this.$form),
                    $buttonsContainer = $('<div class="buttons right"/>').appendTo($footer);
                this.$cancelBtn = $('<div class="btn">' + Craft.t('app', 'Cancel') + '</div>').appendTo($buttonsContainer);
                this.$saveBtn = $('<input class="btn submit" type="submit" value="' + Craft.t('app', 'Save') + '"/>').appendTo($buttonsContainer);
                this.$spinner = $('<div class="spinner hidden"/>').appendTo($buttonsContainer);

                $hudContents = $hudContents.add(this.$form);

                if (!this.hud) {
                    var hudTrigger = (this.settings.hudTrigger || this.$element);

                    this.hud = new Garnish.HUD(hudTrigger, $hudContents, {
                        bodyClass: 'body hudeditor',
                        closeOtherHUDs: false,
                        onShow: $.proxy(this, 'onShowHud'),
                        onHide: $.proxy(this, 'onHideHud'),
                        onSubmit: $.proxy(this, 'save')
                    });

                    this.hud.$hud.data('elementEditor', this);

                    this.hud.on('hide', $.proxy(function () {
                        delete this.hud;
                    }, this));
                } else {
                    this.hud.updateBody($hudContents);
                    this.hud.updateSizeAndPosition();
                }

                // Focus on the first text input
                $hudContents.find('.text:first').trigger('focus');

                this.addListener(this.$cancelBtn, 'click', function () {
                    this.hud.hide();
                });
            }
        },


        reloadForm: function (data, callback) {
            data = $.extend(this.getBaseData(), data);

            Craft.postActionRequest(this.settings.getHtmlAction, data, $.proxy(function (response, textStatus) {
                if (textStatus === 'success') {
                    this.updateForm(response);
                }

                if (callback) {
                    callback(textStatus);
                }
            }, this));
        },

        updateForm: function (response) {
            this.siteId = response.siteId;

            this.$fieldsContainer.html(response.html);

            // Swap any instruction text with info icons
            var $instructions = this.$fieldsContainer.find('> .meta > .field > .heading > .instructions');

            for (var i = 0; i < $instructions.length; i++) {
                $instructions.eq(i)
                    .replaceWith($('<span/>', {
                        'class': 'info',
                        'html': $instructions.eq(i).children().html()
                    }))
                    .infoicon();
            }

            Garnish.requestAnimationFrame($.proxy(function () {
                Craft.appendHeadHtml(response.headHtml);
                Craft.appendFootHtml(response.footHtml);
                Craft.initUiElements(this.$fieldsContainer);
            }, this));
        },

        save: function () {
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
                        this.closeHud();
                        this.onSave(response);
                    } else {
                        this.updateForm(response);
                        Garnish.shake(this.hud.$hud);
                    }
                }
            }, this));
        },

        closeHud: function () {
            this.hud.hide();
            delete this.hud;
        },

        // Events
        // -------------------------------------------------------------------------

        onShowHud: function () {
            this.settings.onShowHud();
            this.trigger('showHud');
        },

        onHideHud: function () {
            this.settings.onHideHud();
            this.trigger('hideHud');
        },

        onBeginLoading: function () {
            if (this.$element) {
                this.$element.addClass('loading');
            }

            this.settings.onBeginLoading();
            this.trigger('beginLoading');
        },

        onEndLoading: function () {
            if (this.$element) {
                this.$element.removeClass('loading');
            }

            this.settings.onEndLoading();
            this.trigger('endLoading');
        },

        onSave: function (response) {
            this.settings.onSave(response);
            this.trigger('save', {
                response: response
            });
        },

        onCreateForm: function ($form) {
            this.settings.onCreateForm($form);
        }
    },
    {
        defaults: {
            hudTrigger: null,

            attributes: null,
            params: null,

            getHtmlAction: null,
            saveAction: null,

            onShowHud: $.noop,
            onHideHud: $.noop,
            onBeginLoading: $.noop,
            onEndLoading: $.noop,
            onCreateForm: $.noop,
            onSave: $.noop,

            validators: []
        }
    }
);