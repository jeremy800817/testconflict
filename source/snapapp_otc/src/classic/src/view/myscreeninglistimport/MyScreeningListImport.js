//
Ext.define('snap.view.myscreeninglistimport.MyScreeningListImport', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myscreeninglistimportview',

    requires: [
        'snap.store.MyScreeningListImport',
        'snap.model.MyScreeningListImport',
        'snap.view.myscreeninglistimport.MyScreeningListImportController',
    ],
    permissionRoot: '/root/system/amla',
    store: { type: 'MyScreeningListImport' },
    controller: 'myscreeninglistimport-myscreeninglistimport',
    toolbarItems: [
        'detail', '|', 'filter', '|',
        { reference: 'importFormBtn', text: 'Import', showToolbarItemText: true, itemId: 'importFormBtn', tooltip: 'Import From URL', iconCls: 'x-fa fa-download', handler: 'importForm' },

    ],
    detailViewWindowHeight: 400,  //Height of the view detail window

    enableFilter: true,
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
        }
    },
    columns: [
        { text: 'ID', hidden: true, dataIndex: 'id', filter: { type: 'int' }, flex: 1 },
        { text: 'Source Type', dataIndex: 'sourcetype', filter: { type: 'string', flex: 2, minWidth: 100, } },
        { text: 'Url', dataIndex: 'url', filter: { type: 'string' }, flex: 4, minWidth: 300 },
        {
            text: 'Status', hidden: true, dataIndex: 'status', flex: 2,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Inactive';
                if (value == '1') return 'Active';
                else return 'Undefined';
            },
        },
        { text: 'Date Imported', dataIndex: 'importedon', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), filter: { type: 'date' }, flex: 2, minWidth: 100, },
        { text: 'Imported By', dataIndex: 'createdbyname', filter: { type: 'string', flex: 1, minWidth: 100, } },
        { text: 'Created On', hidden: true, dataIndex: 'createdon', filter: { type: 'date' }, flex: 1 },
        { text: 'Modified On', hidden: true, dataIndex: 'modifiedon', filter: { type: 'date' }, flex: 1 },
        //{ text: 'Imported By', dataIndex: 'importedon',filter: { type: 'date' } },
        //{ text: 'Created By', dataIndex: 'createdon',filter: { type: 'date' } },
        //{ text: 'Modified By', dataIndex: 'modifiedon',filter: { type: 'date' } },

    ],

    formImport: {
        formDialogWidth: 600,
        controller: 'myscreeninglistimport-myscreeninglistimport',

        formDialogTitle: 'Import From URL',

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'fit',
        },
        enableFormPanelFrame: false,
        formPanelLayout: 'hbox',
        formViewModel: {

        },
        formDialogButtons: [
            {
                text: 'Fetch',
                handler: 'getUrlAction'
            },
            {
                text: 'Close',
                handler: function (btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();

                    owningWindow.myView.getController().gridFormView = null;
                    owningWindow = null;
                }
            }
        ],
        formPanelItems: [{
            xtype: 'form',
            reference: 'importForm',
            layout: { type: 'vbox', align: 'stretch' },
            items: [
                { xtype: 'textfield', fieldLabel: 'Url', name: 'url', reference: 'url', forceSelection: true, allowBlank: false, },
                {
                    items: [
                        {
                            xtype: 'combobox',
                            id: 'amlasources',
                            fieldLabel: 'Source Type',
                            store: [
                                ['UN', 'UN'],
                                ['BNM', 'BNM'],
                                ['MOHA', 'MOHA'],
                            ],
                            queryMode: 'local',
                            remoteFilter: false,
                            name: 'sourcetype',
                            valueField: 'id',
                            displayField: 'code',
                            reference: 'sourcetype',
                            forceSelection: true, allowBlank: false, editable: true
                        },
                    ]
                },
            ],
        }],
    }

});
