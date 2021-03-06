Craft.OrganizationManageAssociations = Garnish.Base.extend({
    $container: null,
    $elementsContainer: null,
    $spinner: null,

    $elements: null,

    $addElementBtn: null,

    elementEditor: null,
    modal: null,
    _initialized: false,


    init: function (settings) {
        this.setSettings(settings, Craft.OrganizationManageAssociations.defaults);

        this.$container = $('#organization-associations');
        this.$elementsContainer = this.$container.children('.elements');;

        this.$addElementBtn = this.$container.find('.btn.add');

        this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$addElementBtn);

        // Apply the storage key prefix
        if (this.settings.modalStorageKey) {
            this.modalStorageKey = 'BaseElementSelectInput.' + this.settings.modalStorageKey;
        }

        if (this.$addElementBtn) {
            this.addListener(this.$addElementBtn, 'activate', 'showModal');
        }

        this.resetElements();
        this._initialized = true;
    },

    getElements: function () {
        return this.$elementsContainer.children();
    },

    resetElements: function () {
        if (this.$elements !== null) {
            this.removeElements(this.$elements);
        } else {
            this.$elements = $();
        }

        this.addElements(this.getElements());
    },

    addElements: function ($elements) {
        $elements = $.makeArray($elements);
        for (var i = 0; i < $elements.length; i++) {
            this.addElement($($elements[i]));
        }
    },
    addElement: function ($element) {

        // Make a couple tweaks
        $element.addClass('removable').removeClass('small')
        $element.prepend('<a class="delete icon" title="' + Craft.t('app', 'Remove') + '"></a>');

        $element.find('.delete').on('click', $.proxy(function (ev) {
            this.dissociate($(ev.currentTarget).closest('.element'));
        }, this));

        if (this.settings.editable) {
            this._handleShowElementEditor = $.proxy(function (ev) {
                var $element = $(ev.currentTarget);
                if (Garnish.hasAttr($element, 'data-editable') && !$element.hasClass('disabled') && !$element.hasClass('loading')) {
                    this.elementEditor = this.createElementEditor($element);
                }
            }, this);

            this.addListener($element, 'dblclick', this._handleShowElementEditor);

            if ($.isTouchCapable()) {
                this.addListener($element, 'taphold', this._handleShowElementEditor);
            }
        }

        this.$elements = this.$elements.add([$element]);
        this.updateAddElementsBtn();
    },
    removeElements: function ($elements) {
        elements = $.makeArray($elements);

        for (var i = 0; i < elements.length; i++) {
            this.removeElement(elements[i]);
        }
    },
    removeElement: function ($element) {
        this.$elements = this.$elements.not([$element]);

        if (this.modal) {
            this.modal.elementIndex.enableElementsById([$element.data('id')]);
        }

        this.updateAddElementsBtn();
    },

    createElementEditor: function ($element) {
        return Craft.createElementEditor(this.settings.elementType, $element);
    },

    createNewElement: function (elementInfo) {
        return elementInfo.$element.clone();
    },

    getDisabledElementIds: function () {
        return this.getSelectedElementIds();
    },

    getSelectedElementIds: function () {
        var ids = [];

        for (var i = 0; i < this.$elements.length; i++) {
            ids.push(this.$elements[i].data('id'));
        }

        return ids;
    },

    canAddMoreElements: function () {
        return (!this.settings.limit || this.$elements.length < this.settings.limit);
    },

    appendElement: function ($element) {
        $element.appendTo(this.$elementsContainer);
    },


    updateAddElementsBtn: function () {
        if (this.canAddMoreElements()) {
            this.enableAddElementsBtn();
        } else {
            this.disableAddElementsBtn();
        }
    },

    disableAddElementsBtn: function () {
        if (this.$addElementBtn && !this.$addElementBtn.hasClass('disabled')) {
            this.$addElementBtn.addClass('disabled');

            if (this.settings.limit == 1) {
                if (this._initialized) {
                    this.$addElementBtn.velocity('fadeOut', Craft.BaseElementSelectInput.ADD_FX_DURATION);
                } else {
                    this.$addElementBtn.hide();
                }
            }
        }
    },

    enableAddElementsBtn: function () {
        if (this.$addElementBtn && this.$addElementBtn.hasClass('disabled')) {
            this.$addElementBtn.removeClass('disabled');

            if (this.settings.limit == 1) {
                if (this._initialized) {
                    this.$addElementBtn.velocity('fadeIn', Craft.BaseElementSelectInput.REMOVE_FX_DURATION);
                } else {
                    this.$addElementBtn.show();
                }
            }
        }
    },

    getActionData: function () {
        var data = $.extend({}, this.settings.actionData);

        return data;
    },

    associate: function ($element, elementInfo) {
        var elementId = $element.data('id');
        if (elementId === null) {
            Craft.cp.displayError(
                Craft.t('organizations', 'Unable to determine element id')
            );
            return;
        }

        this.$spinner.removeClass('hidden');

        var data = this.getActionData();

        data.organization = elementId;
        data.user = this.settings.sourceElementId;

        Craft.actionRequest('POST', this.settings.associateAction, data, $.proxy(function (response, textStatus, jqXHR) {
            this.$spinner.addClass('hidden');

            if (jqXHR.status >= 200 && jqXHR.status <= 299) {
                Craft.cp.displayNotice(
                    Craft.t('organizations', 'Association successful')
                );

                this.appendElement($element);
                this.addElement($element);
                this.updateDisabledElementsInModal();
            } else {
                Craft.cp.displayError(
                    Craft.t('organizations', 'Association failed')
                );
            }
        }, this));

        return true;
    },

    dissociate: function ($element) {
        var elementId = $element.data('id');
        if (elementId === null) {
            return;
        }

        this.$spinner.removeClass('hidden');

        var data = this.getActionData();

        data.organization = elementId;
        data.user = this.settings.sourceElementId;

        Craft.actionRequest('POST', this.settings.dissociateAction, data, $.proxy(function (response, textStatus, jqXHR) {
            this.$spinner.addClass('hidden');

            if (jqXHR.status >= 200 && jqXHR.status <= 299) {
                Craft.cp.displayNotice(
                    Craft.t('organizations', 'Dissociation successful')
                );

                console.log($element);

                this.removeElement($element);

                this.animateElementAway($element, function () {
                    $element.remove();
                });
            } else {
                Craft.cp.displayError(
                    Craft.t('organizations', 'Dissociation failed')
                );
            }
        }, this));

        return true;
    },



    animateElementAway: function ($element, callback) {
        $element.css('z-index', 0);

        var animateCss = {
            opacity: -1
        };
        animateCss['margin-' + Craft.left] = -($element.outerWidth() + parseInt($element.css('margin-' + Craft.right)));

        if (this.settings.viewMode === 'list' || this.$elements.length === 0) {
            animateCss['margin-bottom'] = -($element.outerHeight() + parseInt($element.css('margin-bottom')));
        }

        $element.velocity(animateCss, Craft.BaseElementSelectInput.REMOVE_FX_DURATION, callback);
    },

    getModalSettings: function () {
        return $.extend({
            closeOtherModals: false,
            storageKey: this.modalStorageKey,
            sources: this.settings.sources,
            criteria: this.settings.criteria,
            multiSelect: (this.settings.limit != 1),
            showSiteMenu: this.settings.showSiteMenu,
            disabledElementIds: this.getDisabledElementIds(),
            onSelect: $.proxy(this, 'onModalSelect')
        }, this.settings.modalSettings);
    },

    updateDisabledElementsInModal: function () {
        if (this.modal.elementIndex) {
            this.modal.elementIndex.disableElementsById(this.getDisabledElementIds());
        }
    },

    onModalSelect: function (elements) {
        if (this.settings.limit) {
            // Cut off any excess elements
            var slotsLeft = this.settings.limit - this.$elements.length;

            if (elements.length > slotsLeft) {
                elements = elements.slice(0, slotsLeft);
            }
        }

        for (var i = 0; i < elements.length; i++) {
            var elementInfo = elements[i],
                $element = this.createNewElement(elementInfo);

            // Save association
            this.associate($element);
        }
    },

    showModal: function () {
        // Make sure we haven't reached the limit
        if (!this.canAddMoreElements()) {
            return;
        }

        if (!this.modal) {
            this.modal = this.createModal();
        } else {
            this.modal.show();
        }
    },

    createModal: function () {
        return Craft.createElementSelectorModal(this.settings.elementType, this.getModalSettings());
    },

}, {
    defaults: {
        elementType: "flipbox\\organizations\\elements\\Organization",
        sources: null,
        criteria: {},
        sourceElementId: null,
        limit: null,
        viewMode: 'list',
        showSiteMenu: false,
        modalStorageKey: null,
        modalSettings: {},

        editable: true,

        associateAction: 'organizations/cp/users/associate',
        dissociateAction: 'organizations/cp/users/dissociate',
        actionData: {},
    }
});