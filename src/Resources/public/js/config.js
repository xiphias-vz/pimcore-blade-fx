pimcore.registerNS("pimcore.plugin.bladefx.config");
pimcore.plugin.bladefx.config = Class.create({

    initialize: function () {
        this.getTabPanel();
    },

    activate: function () {
        let tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem(this.getTabPanel());
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "pimcore_plugin_bladefx_config_tab",
                title: t("plugin_pimcore_blade_fx_toolbar"),
                iconCls: "plugin_pimcore_bladefx_icon",
                border: false,
                layout: "border",
                closable: true,
                items: [this.getTree(), this.getGridPanel()]
            });

            const tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("pimcore_plugin_bladefx_config_tab");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("plugin_pimcore_bladefx_config");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    userIsAllowedToCreate: function(adapter) {
        let user = pimcore.globalmanager.get("user");

        if (user.admin || user.isAllowed('plugin_bladefx_admin')) {
            return true;
        }

        return user.isAllowed("plugin_bladefx_adapter_" + adapter);
    },

    getTree: function () {
        if (!this.tree) {
            var store = Ext.create('Ext.data.TreeStore', {
                autoLoad: false,
                autoSync: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/bladefx/config/category-list',
                    reader: {
                        type: 'json'
                    },
                },
                listeners: {
                    load: this.onTreeStoreLoad.bind(this)
                }
            })

            this.tree = new Ext.tree.TreePanel({
                store: store,
                region: "west",
                autoScroll: true,
                animate: true,
                containerScroll: true,
                border: true,
                width: 230,
                split: true,
                root: {
                    id: '0',
                    expanded: true,
                    iconCls: "pimcore_icon_thumbnails"
                },
                rootVisible: false,
                listeners: this.getTreeNodeListeners(),
            });
        }
        return this.tree;
    },

    getGridPanel: function (id) {
        if (!this.gridPanel) {
            Ext.create('Ext.data.Store', {
                storeId: 'reportStore',
                autoLoad: false,
                autoSync: false,
                pageSize: 50,
                fields: [
                    { name: 'rep_id' },
                    { name: 'rep_name' },
                    { name: 'rep_desc' },
                    { name: 'cat_name' },
                    { name: 'is_favorite' }
                ],
                proxy: {
                    type: 'ajax',
                    url: '/admin/bladefx/config/report-list',
                    extraParams: {
                        cat_id: null,
                        start: 0,
                        limit: 50,
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'reportsList',
                        totalProperty: 'total'
                    }
                },
                sorters: [
                    {
                        property: 'is_favorite',
                        direction: 'DESC'
                    },
                ]
            });

            this.gridPanel = new Ext.grid.Panel({
                id: 'blade_fx_grid_panel',
                region: "center",
                title: t("Reports"),
                store: Ext.data.StoreManager.lookup('reportStore'),
                columns: [
                    {
                        text: t('plugin_blade_fx_grid_favorite'),
                        xtype: 'widgetcolumn',
                        flex: 0.2,
                        widget: {
                            xtype: 'button',
                            tooltip: t('plugin_blade_fx_grid_tooltip_favorite'),
                            cls: 'blade-fx_icon_only_btn',
                            handler: function(btn) {
                                let rec = btn.getWidgetRecord();
                                rec.set('is_favorite', !rec.get('is_favorite'));

                                Ext.Ajax.request({
                                    url: '/admin/bladefx/config/favorite-report',
                                    method: 'GET',
                                    params: {
                                        rep_id: rec.get('rep_id'),
                                        favorite: rec.get('is_favorite')
                                    }
                                });

                                let grid = Ext.getCmp('blade_fx_grid_panel');
                                if (grid) {
                                    grid.getStore().reload();
                                }
                            }
                        },
                        onWidgetAttach: function(col, widget, rec) {
                            widget.setIconCls(rec.get('is_favorite')
                                ? 'pimcore_blade-fx_favorite_report'
                                : 'pimcore_blade-fx_not_favorite_report'
                            );
                        },
                        sortable: true
                    },
                    { text: t('plugin_blade_fx_grid_report_id'), dataIndex: 'rep_id', flex: 0.5, sortable: true},
                    { text: t('plugin_blade_fx_grid_report_name'), dataIndex: 'rep_name', flex: 1, sortable: true },
                    { text: t('plugin_blade_fx_grid_report_description'), dataIndex: 'rep_desc', flex: 2 },
                    { text: t('plugin_blade_fx_grid_category'), dataIndex: 'cat_name', flex: 1, sortable: true },
                    {
                        xtype: 'actioncolumn',
                        text: t('plugin_blade_fx_grid_action'),
                        flex: 0.5,
                        items: [
                            {
                                iconCls: 'pimcore_icon_preview_new_window',
                                tooltip: t('plugin_blade_fx_grid_tooltip_preview'),
                                handler: function (grid, rowIndex, colIndex) {
                                    let repId = grid.getStore().getAt(rowIndex);
                                    this.openReportPreview(grid, repId);
                                }.bind(this)
                            }
                        ]
                    }
                ],
                hidden: true,
                border: false,
                bbar: {
                    xtype: 'pagingtoolbar',
                    displayInfo: true,
                    store: Ext.data.StoreManager.lookup('reportStore')
                },
                renderTo: Ext.getBody(),
                listeners: {
                    itemdblclick: this.openReportPreview.bind(this)
                }
            });
        }

        return this.gridPanel;
    },

    onTreeStoreLoad: function (store, records) {
        if (!records || records.length === 0) return;
        let firstNode = records[0];

        if (!firstNode.isLeaf() && firstNode.hasChildNodes()) {
            firstNode = firstNode.firstChild;
        }

        if (firstNode && firstNode.isLeaf()) {
            this.tree.getSelectionModel().select(firstNode);
            this.onTreeNodeClick(null, firstNode);
        }
    },

    getTreeNodeListeners: function () {
        var treeNodeListeners = {
            'itemclick' : this.onTreeNodeClick.bind(this),
        };

        return treeNodeListeners;
    },

    onTreeNodeClick: function (tree, record, item, index, e, eOpts ) {
        if (!record.isLeaf()) {
            return;
        }

        let grid = this.getGridPanel();
        let store = grid.getStore();
        store.getProxy().setExtraParam('cat_id', record.data.id);

        grid.show();
        store.load();
    },

    openReportPreview: function (grid, record) {
        let reportId = record.get('rep_id');

        Ext.Ajax.request({
            url: '/admin/bladefx/config/preview-report',
            method: 'GET',
            params: {
                rep_id: reportId
            },
            success: function (response) {
                let data = Ext.decode(response.responseText);
                let win = new Ext.Window({
                    title: 'Report view',
                    width: '40%',
                    height: '80%',
                    layout: 'fit',
                    modal: true,
                    maximizable: true,
                    items: [{
                        xtype: "component",
                        autoEl: {
                            tag: "iframe",
                            src: data.iframeUrl,
                            style: "border:0; width:100%; height:100%;"
                        }
                    }]
                })

                win.show();
            },
            failure: function () {
                Ext.Msg.alert('Error', 'Could not load report!');
            }
        })
    },
});
