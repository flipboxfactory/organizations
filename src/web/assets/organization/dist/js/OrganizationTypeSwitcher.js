Craft.OrganizationTypeSwitcher = Garnish.Base.extend({
    $container: null,

    $activeButton: null,
    activeMenuBtn: null,

    $availableButton: null,
    availableMenuBtn: null,

    $spinner: null,
    $fields: null,
    $tabs: null,
    $sites: null,
    $sidebar: null,

    init: function (settings) {
        this.$container = $('#types');
        this.$fields = $('#fields');
        this.$tabs = $('#tabs');
        this.$sites = $('#sites');
        this.$sidebar = $('#enabled');

        this.setSettings(settings, Craft.OrganizationTypeSwitcher.defaults);

        this.$activeButton = this.$container.find('#active');
        this.$availableButton = this.$container.find('#available');

        this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$availableButton);

        this.activeMenuBtn = this.$activeButton.menubtn().data('menubtn');
        this.activeMenuBtn.on('optionSelect', $.proxy(this, 'onTypeSelect'));

        this.availableMenuBtn = this.$availableButton.menubtn().data('menubtn');
        this.availableMenuBtn.on('optionSelect', $.proxy(this, 'onAvailableSelect'));

        this.changeToUrlTypeHandleOption();
    },

    // When a type is selected
    onTypeSelect: function (ev) {
        var $option = $(ev.option);
        if ($option.hasClass('disabled')) {
            return;
        }

        this.changeType($option);
    },

    onAvailableSelect: function (ev) {
        var $option = $(ev.option);
        if ($option.hasClass('disabled')) {
            return;
        }

        if ($option.children('.status').hasClass('active')) {
            this.dissociateType($option.data('type'), $option.data('organization'));
        } else {
            this.associateType($option.data('type'), $option.data('organization'));
        }
    },

    findActiveAssignedContainer: function () {
        return this.activeMenuBtn.menu.$container.find('#assigned');
    },

    findActiveAssignedMenuList: function () {
        return $(this.activeMenuBtn.menu.$menuList[1]);
    },

    showAssignedContainer: function () {
        var $div = this.findActiveAssignedContainer();
        if ($div.length) {
            if (this.activeMenuBtn.menu.$menuList.children('li').length === 1) {
                $div.hide();
            } else {
                $div.show();
            }
        }
    },

    changeType: function ($option) {

        // Mark all as unselected
        $(this.activeMenuBtn.menu.$menuList)
            .find('.sel')
            .removeClass('sel');

        // Select
        $option.addClass('sel');

        var value = $option.attr('data-id');
        var label = $option.html();

        // Change header/hidden input
        this.$activeButton.html('<input type="hidden" name="type" value="' + value + '">' + label);

        // Replace, tabs + content
        this.replaceContent();
    },

    getActiveTypeId: function () {

        var $input = this.$activeButton.find('input[name="type"]');

        if (!$input.length) {
            return;
        }

        return $input.val();

    },

    ensureAvailableSelected: function () {

        // Get option from menu list
        var $active = this.getActiveOption(this.getActiveTypeId());
        if ($active) {
            return;
        }

        $active = this.getFirstActiveOption();
        if (!$active) {
            return;
        }

        this.changeType($active);

    },

    getAvailableOption: function (type) {
        var $option = this.availableMenuBtn.menu.$options.filter('[data-type="' + type + '"]');
        if (!$option.length) {
            return;
        }

        return $option;
    },

    removeActiveOption: function (id) {
        // Hidden input
        var $input = this.$availableButton.find('input[name="types[]"][value="' + id + '"]');
        if ($input.length) {
            $input.remove();
        }

        $.each(this.activeMenuBtn.menu.$options, $.proxy(function (index, item) {
            var $item = $(item);
            if ($item.attr('data-id') === id) {
                $item.parent('li').remove();
                this.activeMenuBtn.menu.$options.splice(index, 1);
                return true;
            }
        }, this));
    },

    getActiveOption: function (id) {
        var $active = this.activeMenuBtn.menu.$options.filter('[data-id="' + id + '"]');
        if (!$active.length) {
            return;
        }

        return $active;
    },

    getFirstActiveOption: function () {
        var $active = this.activeMenuBtn.menu.$options.first();
        if (!$active.length) {
            return;
        }

        return $active;
    },

    changeToUrlTypeHandleOption: function () {
        var typeHandle = window.location.pathname.split('/').pop();
        var $active = this.activeMenuBtn.menu.$options.filter('[data-handle="' + typeHandle + '"]');

        if ($active.length) {
            this.changeType($active);
        }
    },

    associateType: function (type, organization) {
        var $option = this.getAvailableOption(type);
        if (!$option) {
            return;
        }

        // Update status
        $option.children('.status').addClass('active');

        var value = $option.attr('data-type');
        var label = $("<div/>").html($option.html()).text();

        var $item = $('<a data-id="' + value + '">' + label + '</a>');

        // Append to menu lust
        this.findActiveAssignedMenuList().append(
            $('<li/>')
                .attr('id', 'type-' + $option.attr('data-type'))
                .html($item)
        );
        this.activeMenuBtn.menu.addOptions($item);

        // Add hidden input
        if (value) {
            this.$availableButton.append(
                $('<input />').attr("name", "types[]")
                    .attr("type", "hidden")
                    .val(value)
            );
        }
        this.showAssignedContainer();
    },

    dissociateType: function (type, organization) {
        var $option = this.getAvailableOption(type);
        if (!$option) {
            return;
        }

        // Update status
        $option.children('.status').removeClass('active');

        var value = $option.attr('data-type');

        // Remove
        this.removeActiveOption(value);

        // Remove hidden input

        this.showAssignedContainer();
        this.ensureAvailableSelected();
    },

    replaceContent: function () {
        this.$spinner.removeClass('hidden');

        Craft.actionRequest(
            'POST',
            this.settings.action,
            Craft.cp.$primaryForm.serialize(),
            $.proxy(
                function (response, textStatus, jqXHR) {
                    this.$spinner.addClass('hidden');
                    if (jqXHR.status === 200) {
                        // Copy the user tab + content
                        var $usersTabLink = this.$tabs.find('ul li a#tab-users');
                        var $usersTab = $usersTabLink.parent('li').clone();

                        // Fields (except user tab content)
                        this.$fields.children(':not(#user-index)').remove();
                        this.$fields.prepend(response.fieldsHtml);

                        // Tabs
                        this.$tabs.html(response.tabsHtml);
                        this.$tabs.find('ul').append($usersTab);

                        // Replace content
                        this.$sites.html(response.sitesHtml);
                        this.$sidebar.html(response.sidebarHtml);

                        Craft.appendHeadHtml(response.headHtml);
                        Craft.appendFootHtml(response.footHtml);

                        // Init
                        Craft.cp.initTabs();
                        Craft.initUiElements(this.$fields);
                        Craft.initUiElements(this.$sites);
                        Craft.initUiElements(this.$sidebar);
                        // Craft.initUiElements($usersContent);

                        // Update the slug generator with the new title input
                        if (typeof slugGenerator !== 'undefined') {
                            slugGenerator.setNewSource('#title');
                        }
                    } else {
                        Craft.cp.errorNotice(
                            Craft.t('organizations', 'Failed to load content.')
                        );
                    }
                },
                this
            )
        );
    }

}, {
    defaults: {
        action: 'organizations/cp/organizations/switch-type'
    }
});