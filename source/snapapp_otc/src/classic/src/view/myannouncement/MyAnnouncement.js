Ext.define('snap.view.myannouncement.MyAnnouncement', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myannouncementview',

    requires: [

        'snap.store.MyAnnouncement',
        'snap.model.MyAnnouncement',
        'snap.view.myannouncement.MyAnnouncementController',
        'snap.view.myannouncement.MyAnnouncementModel',

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/system/myannouncement',
    store: { type: 'MyAnnouncement' },
    controller: 'myannouncement-myannouncement',
    viewModel: {
        type: 'myannouncement-myannouncement'
    },
    enableFilter: true,
    toolbarItems: [
        'add', 'edit', 'detail', '|', 'delete', 'filter', '|',
        {
            reference: 'approveAnnouncementBtn', text: 'Approve', itemId: 'approveAnnouncement', iconCls: 'x-fa fa-check', handler: 'approveAnnouncement', validSelection: 'single',
            listeners: {
                afterrender: function (c) {
                    Ext.create('Ext.tip.ToolTip', {
                        target: c.getEl(),
                        html: 'Approve announcement'
                    });
                }
            }
        },
        {
            reference: 'disableAnnouncementBtn', text: 'Disable', itemId: 'disableAnnouncement', iconCls: 'x-fa fa-times', handler: 'disableAnnouncement', validSelection: 'single',
            listeners: {
                afterrender: function (c) {
                    Ext.create('Ext.tip.ToolTip', {
                        target: c.getEl(),
                        html: 'Disable announcement'
                    });
                }
            }
        },
        //'detail', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
    ],

    listeners: {
        afterrender: function () {
            var me = this;
            me.store.sorters.clear();
            me.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns = me.query('gridcolumn');
            columns.find(obj => obj.text === 'ID').setVisible(false);
            me.checkActionPermissions();
        },

    },
    checkActionPermissions: function () {
        var approvePermission = snap.getApplication().hasPermission('/root/system/myannouncement/approve');
        var disablePermission = snap.getApplication().hasPermission('/root/system/myannouncement/disable');
        var btnApproveAnnouncement = Ext.ComponentQuery.query('#approveAnnouncement')[0];
        var btnDisableAnnouncement = Ext.ComponentQuery.query('#disableAnnouncement')[0];

        if (true == approvePermission) {
            btnApproveAnnouncement.show();
        } else {
            btnApproveAnnouncement.hide();
        }

        if (true == disablePermission) {
            btnDisableAnnouncement.show();
        } else {
            btnDisableAnnouncement.hide();
        }
    },

    columns: [
        { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, flex: 1 },
        { text: 'Code', dataIndex: 'code', filter: { type: 'string' }, flex: 1 },
        // { text: 'Title', dataIndex: 'title', filter: { type: 'string' }, flex: 1 },
        // { text: 'Content', dataIndex: 'content', filter: { type: 'string' }, flex: 1 },
        { text: 'Locales', dataIndex: 'locales', filter: { type: 'string' }, flex: 1 },
        { text: 'Type', dataIndex: 'type', filter: { type: 'string' }, flex: 1 },
        { text: 'Start On', dataIndex: 'displaystarton', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0, hidden: true },
        { text: 'End On', dataIndex: 'displayendon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0, hidden: true },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 1, hidden: true },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 1, hidden: true },
        { text: 'Approved On', dataIndex: 'approvedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 1, hidden: true },
        { text: 'Disabled On', dataIndex: 'disabledon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 1, hidden: true },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true },
        { text: 'Approved By', dataIndex: 'approvedbyname', filter: { type: 'string' }, hidden: true },
        { text: 'Disabled By', dataIndex: 'disabledbyname', filter: { type: 'string' }, hidden: true },
        {
            text: 'Status', dataIndex: 'status', flex: 2,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Pending'],
                    ['2', 'Approved'],
                    ['3', 'Queued'],
                    ['4', 'Completed'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Inactive';
                if (value == '1') return 'Pending';
                if (value == '2') return 'Approved';
                if (value == '3') return 'Queued';
                if (value == '4') return 'Completed';
                else return 'Queued';
            },
        },

    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 500,
    style: 'word-wrap: normal',
    detailViewSections: { default: 'Properties' },
    detailViewUseRawData: true,

    formConfig: {
        controller: 'myannouncement-myannouncement',
        viewModel: {
            data: {
            }
        },

        formDialogWidth: 950,
        formDialogTitle: 'Account Holder Announcement',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 460,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {
                xtype: 'tabpanel',
                flex: 1,
                reference: 'myannouncementtab',
                items: [
                    {
                        title: 'Announcement Details',
                        layout: 'vbox',
                        margin: '28 8 8 18',
                        width: 550,
                        items: [
                            { xtype: 'hidden', hidden: true, name: 'id' },
                            { xtype: 'textfield', fieldLabel: 'Code', name: 'code', width: '90%' },
                            {
                                xtype: 'combobox', fieldLabel: 'Type', store: { type: 'array', fields: ['type'] }, queryMode: 'local', remoteFilter: false,
                                name: 'type', valueField: 'type', displayField: 'type', reference: 'type', forceSelection: false, editable: false, allowBlank: false
                            },
                            { xtype: 'datefield', fieldLabel: 'Start On', name: 'displaystarton', width: '90%', format: 'Y-m-d H:i:s', editable: false, required: true, allowBlank: false },
                            { xtype: 'datefield', fieldLabel: 'End On', name: 'displayendon', width: '90%', format: 'Y-m-d H:i:s', editable: false, required: true, allowBlank: false },
                        ]
                    },
                    {
                        title: 'Announcement Content',
                        layout: 'fit',
                        // autoScroll: true,
                        margin: '1',
                        items: [
                            {
                                xtype: 'textfield',
                                hidden: true,
                                name: 'myAnnouncementTranslationParams',
                                reference: 'myAnnouncementTranslationParams',
                                itemId: 'myAnnouncementTranslationParams',
                                store: { type: 'array' },
                            },
                            {
                                reference: 'myAnnouncementTranslation',
                                name: 'myAnnouncementTranslation',
                                itemId: 'myAnnouncementTranslation',
                                xtype: 'gridpanel',
                                title: '',
                                store: Ext.create('snap.store.MyAnnouncementTranslation', {
                                    storeId: 'myAnnouncementTranslationStore',
                                }),
                                tbar: [
                                    {
                                        itemId: 'addrec',
                                        text: 'Add',
                                        iconCls: 'fa fa-plus-circle',
                                        plain: true,
                                        handler: 'paramAddClick'
                                    },
                                    {
                                        itemId: 'removerec',
                                        text: 'Remove',
                                        iconCls: 'fa fa-minus-circle',
                                        plain: true,
                                        handler: 'paramDelClick',
                                        disabled: true
                                    }
                                ],
                                listeners: { viewReady: 'myAnnouncementTranslationViewReady', selectionchange: 'paramsSelectionChange' },
                                sortableColumns: false,
                                columns: [
                                    {
                                        text: 'Language', dataIndex: 'language', width: '15%',
                                        editor: {
                                            xtype: 'combobox', store: Ext.create('snap.store.MyLocale'), queryMode: 'local', remoteFilter: true,
                                            name: 'language', valueField: 'value', displayField: 'name', forceSelection: true, editable: false, allowBlank: false
                                        }
                                    },
                                    { text: 'Title', dataIndex: 'title', editor: { name: 'title', xtype: 'textfield', allowBlank: false }, width: '25%' },
                                    { text: 'Content', dataIndex: 'content', editor: { name: 'content', xtype: 'textarea', allowBlank: false }, width: '60%' },
                                ],
                                selType: 'rowmodel',
                                plugins: [
                                    {
                                        xclass: 'Ext.grid.plugin.RowEditing',
                                        clicksToEdit: 1,
                                        autoCancel: false,
                                        pluginId: 'editedRow1',
                                        id: 'editedRow1'
                                    }
                                ]

                            },
                        ]
                    }
                ]
            }
        ]
    },


});
