Ext.define('snap.view.user.UserSwitchBranchLog_BSN', {
    extend: 'Ext.panel.Panel',
    xtype: 'userswitchbranchlog_BSN',

    permissionRoot: "/root/" + PROJECTBASE.toLowerCase() + "/userswitchbranchlog/list",
    layout: 'fit',
    scrollable: true,
    bodyPadding: 10,

    items: {
        cls: Ext.baseCSSPrefix + 'shadow',

        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },

        defaults: {
            frame: true,
            bodyPadding: 10,
            margin: 10,
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',

        items: [
            {
                xtype: 'panel',
                title: 'User Switch Branch Log',
                layout: 'vbox',
                collapsible: true,
                margin: "10 0 0 0",
                scrollable: true,
                flex: 1,
                items: [
                    {
                        xtype: 'otcorderview',
                        flex: 1,
                        enableFilter: true,
                        partnercode: PROJECTBASE,
                        scrollable: true,
                        controller: 'user-user',
                        toolbarItems: [
                            'detail', '|', 'filter', '|',
                            {
                                xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
                            },
                            {
                                xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
                            },
                            {
                                iconCls: 'x-fa fa-redo-alt', style: "width: 130px;", text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth: 'auto'
                            },
                            {
                                text: 'Download', cls: '', tooltip: 'Download Report', iconCls: 'x-fa fa-download', reference: 'userswitchbranchlog', handler: 'getuserswitchbranchlogReport', showToolbarItemText: true, printType: 'xlsx',
                            },
                        ],
                        reference: 'userswitchbranchlog',
                        store: {
                            type: 'UserSwitchBranchLog',
                            proxy: {
                                type: 'ajax',
                                url: 'index.php?hdl=otcuserswitchbranchlog&action=list&partnercode=' + PROJECTBASE,
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                        },

                        columns: [
                            { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
                            { text: 'User ID', dataIndex: 'userid', filter: { type: 'int' }, minwidth: 160 , hidden: true},
                            { text: 'User Name', dataIndex: 'username', filter: { type: 'string' }, minwidth: 160 },
                            { text: 'From Branch ID', dataIndex: 'frompartnerid', filter: { type: 'int' }, minwidth: 160, hidden: true},
                            { text: 'From Branch Name', dataIndex: 'frompartnername', filter: { type: 'string' }, minwidth: 250 },
                            { text: 'To Branch ID', dataIndex: 'topartnerid', filter: { type: 'int' }, minwidth: 160, hidden: true },
                            { text: 'To Branch Name', dataIndex: 'topartnername', filter: { type: 'string' }, minwidth: 250 },

                            { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, minWidth: 160 },
                            { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 160 },
                            { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, minWidth: 160 },
                            { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 160 },
                            {
                                text: 'Status', dataIndex: 'status', minWidth: 100, hidden: true,
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Inactive'],
                                        ['1', 'Active'],
                                    ],
                                }, renderer: function (value) {
                                    switch (value) {
                                        case 0:
                                            return 'Inactive';
                                        case 1:
                                            return 'Active';
                                        default:
                                            return 'Unidentified';
                                    }
                                }
                            },
                        ],
                    },
                ]
            },
        ]
    },
});
