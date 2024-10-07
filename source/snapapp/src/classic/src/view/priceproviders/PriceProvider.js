Ext.define('snap.view.priceproviders.PriceProvider', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'priceproviderview',

    requires: [
        'snap.store.PriceProvider',
        'snap.model.PriceProvider',
        'snap.view.priceproviders.PriceProviderController',
        'snap.view.priceproviders.PriceProviderModel',
    ],

    controller: 'priceprovider-priceprovider',
    viewModel: {
        type: 'priceprovider-priceprovider',
    },
    store: { type: 'PriceProvider', autoLoad: true },
    //store: [{ 'id' : 'adadada'}],
    permissionRoot: '/root/system/priceprovider',
    enableFilter: true,
    toolbarItems: [
        'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        //'detail', 'filter',
        { reference: 'getPriceProviderStatusButton', text: 'Get Status', itemId: 'getPriceProviderStatus', tooltip: 'Get Status', iconCls: 'x-fa fa-list-alt', handler: 'getPriceProviderStatus', validSelection: 'single' },
        '|',
        { reference: 'startButton', text: 'Start Selected', itemId: 'startPriceProvider', tooltip: 'Start Price Provider', iconCls: 'x-fa fa-play', handler: 'startPriceProvider', validSelection: 'single',showToolbarItemText: true, },
        { reference: 'stopButton', text: 'Stop Selected', itemId: 'stopPriceProvider', tooltip: 'Stop Price Provider', iconCls: 'x-fa fa-stop', handler: 'stopPriceProvider', validSelection: 'single', showToolbarItemText: true,  },
        '|',
        { reference: 'stopButtonDealer', text: 'Dealers', itemId: 'togglePriceProviderDealer', tooltip: 'Start/Stop Dealers Only', iconCls: 'x-fa fa-user', handler: 'toggleSpecificPriceProviderGroup', value: 'Dealer', showToolbarItemText: true, },
        { reference: 'stopButtonBanking', text: 'Banking', itemId: 'togglePriceProviderBanking', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-university  ', handler: 'toggleSpecificPriceProviderGroup', value: 'Banking', showToolbarItemText: true, },
        { reference: 'stopButtonKoperasi', text: 'Koperasi', itemId: 'togglePriceProviderKoperasi', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-building', handler: 'toggleSpecificPriceProviderGroup', value: 'Koperasi', showToolbarItemText: true, },
        { reference: 'stopButtonEwallet', text: 'E-Wallet', itemId: 'togglePriceProviderEwallet', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-credit-card', handler: 'toggleSpecificPriceProviderGroup', value: 'E-wallet', showToolbarItemText: true, },
        { reference: 'stopButtonGlc', text: 'GLC', itemId: 'togglePriceProviderGlc', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-user-secret', handler: 'toggleSpecificPriceProviderGroup', value: 'GLC', showToolbarItemText: true, },
        { reference: 'stopButtonBuyback', text: 'Buyback', itemId: 'togglePriceProviderBuyback', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-shopping-bag', handler: 'toggleSpecificPriceProviderGroup', value: 'Buyback', showToolbarItemText: true, },
        { reference: 'stopButtonOther', text: 'Others', itemId: 'togglePriceProviderOther', tooltip: 'Start/Stop Price Provider', iconCls: 'x-fa fa-users', handler: 'toggleSpecificPriceProviderGroup', value: 'Others', showToolbarItemText: true, },

        //{reference: 'restartButton', text: 'Restart', itemId: 'restartPriceProvider', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-repeat', handler: 'restartPriceProvider', validSelection: 'single' }

        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
    ],

    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },
    columns: [
        //{text: 'ID', dataIndex: 'id', filter: {type: 'int'}, flex: 1,},
        {
            text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, minWidth: 80,
        },
        { text: 'Code', dataIndex: 'code', filter: { type: 'string' }, minWidth: 100,  renderer: function (val, m, record) {

            if (record.data.isrunning == 0) {
                //return 'Not Running';
                return '<span style="margin-right:5px;color:#c23f10" class="fa fa-square"></span><span>' + val + '</span>';
            } else if (record.data.isrunning == 1) {
                //return 'Is Running';
                return '<span style="margin-right:5px;color:#0aad3b" class="fa fa-square"></span><span>' + val + '</span>';
            } else {
                // Inactive    
                return '<span style="margin-right:5px;color:#bdb9b7" class="fa fa-square"></span><span>' + val + '</span>';
            }
        } },
        //{text: 'Returnstatus', dataIndex: 'isrunning', filter: {type: 'string'}, flex: 1,},
        { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, flex: 1, minWidth: 180 },
        { text: 'Index', dataIndex: 'index', filter: { type: 'string' }, flex: 1, minWidth: 180 },
        //{ text: 'Price Source', dataIndex: 'pricesourceid', filter: { type: 'int' }, hidden: true, minWidth: 130 },
        { text: 'Price Source Code', dataIndex: 'pricesourcecode', filter: { type: 'string' }, minWidth: 150 },
        { text: 'Product Category Name', dataIndex: 'productcategoryname', filter: { type: 'int' }, minWidth: 180 },
        { text: 'Pullmode', dataIndex: 'pullmode', filter: { type: 'int' }, minWidth: 100 },
        //{ text: 'Currency', dataIndex: 'currencyid', filter: { type: 'int' }, hidden: true, minWidth: 100 },
        { text: 'Currency', dataIndex: 'currencycode', filter: { type: 'string' }, minWidth: 100 },
        { text: 'Whitelist IP', dataIndex: 'whitelistip', filter: { type: 'string' }, minWidth: 150 },

        { text: 'URL', dataIndex: 'url', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Connect Info', dataIndex: 'connectinfo', filter: { type: 'string' }, minWidth: 200 },
        { text: 'Lapse Time Allowance', dataIndex: 'lapsetimeallowance', filter: { type: 'int' }, minWidth: 160 },

        { text: 'Future Order Strategy', dataIndex: 'futureorderstrategy', filter: { type: 'string' }, minWidth: 160 },
        { text: 'Future Order Params', dataIndex: 'futureorderparams', filter: { type: 'string' }, minWidth: 150 },
        { text: 'Provider Group', dataIndex: 'providergroupcode', filter: { type: 'string' }, minWidth: 150 },

        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, flex: 1, hidden: true, minWidth: 100 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 100 },

        {
            text: 'Status', dataIndex: 'status',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '1') return 'Active';
                else return 'Inactive';
            },
        },

        //{text: 'providername', dataIndex: 'providername', filter: {type: 'string'}, flex: 1,},
        //{text: 'createdbyname', dataIndex: 'createdbyname', filter: {type: 'string'}, flex: 1,},
        //{text: 'modifiedbyname', dataIndex: 'modifiedbyname', filter: {type: 'string'}, flex: 1,},

    ],
    //formClass: 'snap.view.PriceValidation.pricevalidationGridForm'
    /*formConfig: {
        formDialogTitle: 'Price Stream',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            labelWidth: 60,
            required: true
        },
        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
        ]
    },*/
    /////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

    formConfig: {
        controller: 'priceprovider-priceprovider',
        viewModel: {
            data: {
            }
        },

        formDialogWidth: 950,
        formDialogTitle: 'Price Provider',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 560,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {
                items:[{
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'container',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    items: [
                        {
                            items: [
                                { xtype: 'fieldset', title: 'Price Source Settings', collapsible: false,
                                    default: { labelWidth: 90, layout: 'hbox'},
                                    items: [
                                        { xtype: 'hidden', hidden: true, name: 'id' },
                                       // { xtype: 'textfield', fieldLabel: 'Price Source', name: 'pricesourceid', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Price Source Code', name: 'pricesourcecode', width: '90%' },
                                        {
                                            xtype: 'combobox', fieldLabel: 'Price Source',
                                            store: {
                                                autoLoad: true,
                                                type: 'PriceSourceProviders',
                                                sorters: 'value'
                                            },
                                            queryMode: 'local',
                                            remoteFilter: false,
                                            name: 'pricesourceid',
                                            valueField: 'id',
                                            displayField: 'value',
                                            forceSelection: true, editable: false,
                                            renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                                var productitems =Ext.getStore('pricesourceproviders').load();
                                                console.log(productitems);
                                                var catRecord = productitems.findRecord('id', value);
                                                return catRecord ? catRecord.get('value') : '';
                                            },
                                        },
                                    
                                    ]
                                },
                                { xtype: 'fieldset', title: 'Connection Settings', collapsible: false,
                                    default: { labelWidth: 90, layout: 'hbox'},
                                    items: [
                                        
                                        { xtype: 'textfield', fieldLabel: 'White List IP', name: 'whitelistip', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'URL', name: 'url', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Connect Info', name: 'connectinfo', width: '90%' },                      
                                       
                                    ]
                                }
                            ],
                        },
                        {
                            items: [
                                { xtype: 'fieldset', title: 'Price Provider Settings', collapsible: false,
                                    default: { labelWidth: 90, layout: 'hbox'},
                                    items: [
                                        //{ xtype: 'hidden', hidden: true, name: 'id' },
                                        { xtype: 'numberfield', fieldLabel: 'Index - sorting', name: 'index', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Code', name: 'code', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Name', name: 'name', width: '90%' },
                                        //{ xtype: 'textarea', fieldLabel: 'Price Source', name: 'pricesourceid', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Price Source Code', name: 'pricesourcecode', width: '90%' },
                                        {
                                            xtype: 'combobox', fieldLabel: 'Product Category', 
                                            store: {
                                                autoLoad: true,
                                                type: 'ProductCategories',
                                                sorters: 'value'
                                            },
                                             queryMode: 'local', remoteFilter: false,
                                            name: 'productcategoryid', valueField: 'id', displayField: 'value',
                                            forceSelection: true, editable: false,
                                            renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {     
                                                var productitems =Ext.getStore('productcategories').load();                                                                              
                                                var catRecord = productitems.findRecord('id', value);                                       
                                                return catRecord ? catRecord.get('value') : ''; 
                                            }, 
                                        }, 
                                        //{ xtype: 'textfield', fieldLabel: 'Product Category Name', name: 'productcategoryname', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Pullmode', name: 'pullmode', width: '90%' },
                                        {
                                            xtype: 'combobox', fieldLabel: 'Currency',
                                            store: {
                                                autoLoad: true,
                                                type: 'CurrencyProviders',
                                                sorters: 'value'
                                            },
                                            queryMode: 'local',
                                            remoteFilter: false,
                                            name: 'currencyid',
                                            valueField: 'id',
                                            displayField: 'value',
                                            forceSelection: true, editable: false,
                                            renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                                var productitems =Ext.getStore('currencyproviders').load();
                                                console.log(productitems);
                                                var catRecord = productitems.findRecord('id', value);
                                                return catRecord ? catRecord.get('value') : '';
                                            },
                                        },
                                        //{ xtype: 'textfield', fieldLabel: 'Currency ID', name: 'currencyid', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Currency', name: 'currencycode', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'White List IP', name: 'whitelistip', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Connect Info', name: 'connectinfo', width: '90%' },
                                        {
                                            xtype: 'combobox', fieldLabel: 'Provider Group',
                                            store: {
                                                autoLoad: true,
                                                type: 'ProviderGroup',
                                                sorters: 'value'
                                            },
                                            queryMode: 'local',
                                            remoteFilter: false,
                                            name: 'providergroupid',
                                            valueField: 'id',
                                            displayField: 'value',
                                            forceSelection: true, editable: false,
                                            renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                                var productitems =Ext.getStore('providergroup').load();
                                                console.log(productitems);
                                                var catRecord = productitems.findRecord('id', value);
                                                return catRecord ? catRecord.get('value') : '';
                                            },
                                        },
                                        { xtype: 'textfield', fieldLabel: 'Lapse Time Allowance', name: 'lapsetimeallowance', width: '90%',
                                        maskRe: /[0-9.-]/,
                                        validator: function(v) {
                                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                        },},
                                        { xtype: 'textfield', fieldLabel: 'Future Order Strategy', name: 'futureorderstrategy', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Future Order Params', name: 'futureorderparams', width: '90%' },
                                        /*{ xtype: 'radiogroup', fieldLabel: 'Status', width: '90%',
                                            items: [{
                                                boxLabel  : 'Active',
                                                name      : 'status',
                                                inputValue: '1'
                                            },],
                                            value : 1,
                                            
                                        }*/
                                        { xtype: 'hidden', hidden: true, name: 'status', value: 1 },
                                    ]
                                }
                            ],
                        }
                    ]
                }]
            }
        ]
    },


});
