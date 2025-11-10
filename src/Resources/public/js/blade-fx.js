pimcore.registerNS("pimcore.plugin.bladefx");
pimcore.plugin.bladefx = Class.create({
    getClassName: function () {
        return "pimcore.plugin.bladefx";
    },

    initialize: function () {
        if (pimcore.events.preMenuBuild) {
            document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
        } else {
            document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
        }

        document.addEventListener("pimcore.perspectiveEditor.permissions.structure.load", (e) => {
            if (e.detail.context === 'toolbar') {
                e.detail.structure['bladefx'] = {};
            }
        });

        document.addEventListener("pimcore.perspectiveEditor.permissions.load", (e) => {
            const context = e.detail.context;
            const menu = e.detail.menu;
            const permissions = e.detail.permissions;

            if (context === 'toolbar' && menu === 'bladefx') {
                if (permissions[context][menu] === undefined) {
                    permissions[context][menu] = [];
                }
                if (permissions[context][menu].indexOf('hidden') === -1) {
                    permissions[context][menu].push('hidden');
                }
            }
        });
    },

    preMenuBuild: function (e) {
        const perspectiveCfg = pimcore.globalmanager.get("perspective");

        if (perspectiveCfg.inToolbar("bladefx") === false) {
            return
        }

        const user = pimcore.globalmanager.get("user");
        if (user.admin || user.isAllowed("plugin_bladefx_config")) {

            let menu = e.detail.menu;

            menu.bladefx = {
                label: t('plugin_pimcore_blade_fx_toolbar'),
                iconCls: 'pimcore_main_nav_icon_bladefx',
                priority: 55,
                shadow: false,
                handler: this.openBladeFx,
                cls: "pimcore_navigation_flyout",
                noSubmenus: true
            };
        }
    },

    openBladeFx: function(e) {
        try {
            pimcore.globalmanager.get("plugin_pimcore_bladefx_config").activate();
        } catch (e) {
            pimcore.globalmanager.add("plugin_pimcore_bladefx_config", new pimcore.plugin.bladefx.config());
        }
    },

    pimcoreReady: function(e) {
        const perspectiveCfg = pimcore.globalmanager.get("perspective");

        if (perspectiveCfg.inToolbar("bladefx") === false) {
            return
        }

        const user = pimcore.globalmanager.get("user");
       if (user.admin || user.isAllowed("plugin_datahub_config")) {

            let navEl = Ext.get('pimcore_menu_search').insertSibling('<li id="pimcore_menu_bladefx" data-menu-tooltip="'
                + t('plugin_pimcore_blade_fx_toolbar') +
                '" class="pimcore_menu_item pimcore_menu_needs_children"><img alt="bladefx" src="/bundles/pimcorebladefx/logo.webp"></li>', 'before');

            navEl.on('mousedown', function () {
                try {
                    pimcore.globalmanager.get("plugin_pimcore_bladefx_config").activate();
                } catch (e) {
                    pimcore.globalmanager.add("plugin_pimcore_bladefx_config", new pimcore.plugin.bladefx.config());
                }
            });

            pimcore.helpers.initMenuTooltips();
        }
    }
});

var bladefxPlugin = new pimcore.plugin.bladefx();
