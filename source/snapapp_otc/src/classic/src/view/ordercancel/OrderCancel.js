Ext.define('snap.view.ordercancel.OrderCancel',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'ordercancelview',

    requires: [
        'snap.store.OrderCancel',
        'snap.model.OrderCancel',
        'snap.view.ordercancel.OrderCancelController',
        'snap.view.ordercancel.OrderCancelModel'
    ],
    permissionRoot: '/root/trading/futureordercancel',
    store: { type: 'OrderCancel' },
    controller: 'ordercancel-ordercancel',

    viewModel: {
        type: 'ordercancel-ordercancel'
    },

    detailViewWindowHeight: 400,

    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
    ],

    columns: [
        { text: 'ID',  dataIndex: 'id', filter: {type: 'string'}, flex: 1 },
        { text: 'Order ID',  dataIndex: 'orderid', filter: {type: 'string'} , hidden: true, flex: 2 },
        { text: 'Partner ID',  dataIndex: 'partnerid', filter: {type: 'string'} , hidden: true, flex: 2 },
        { text: 'Buyer ID',  dataIndex: 'buyerid', filter: {type: 'string'} , hidden: true, flex: 2 },
        { text: 'Partner Refferal ID',  dataIndex: 'partnerrefid', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Order Queue No',  dataIndex: 'orderqueueno', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Salesperson Name',  dataIndex: 'salespersonid', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'API Version',  dataIndex: 'apiversion', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Order Type',  dataIndex: 'ordertype', flex: 3,
                filter: {
                    type: 'combo',
                    store: [
                        ['CompanySell', 'CompanySell'],
                        ['CompanyBuy', 'CompanyBuy'],
                        ['CompanyBuyBack', 'CompanyBuyBack'],
                    ],
                    renderer: function(value, rec){
                        if(value=='CompanySell') return 'CompanySell';
                        else if(value=='CompanyBuy') return 'CompanyBuy';
                        else return 'CompanyBuyBack';
                    },
                },
        },
        { text: 'Queue Type',  dataIndex: 'queuetype', filter: {type: 'string'  } , flex: 3 },
        { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 3 },
        { text: 'Product ID',  dataIndex: 'productid', filter: {type: 'string'} , hidden: true, flex: 2 },
        { text: 'Price Target',  dataIndex: 'pricetarget', filter: {type: 'string'} , flex: 2 },
        { text: 'By Weight',  dataIndex: 'byweight', filter: {type: 'string'} , flex: 2 },
        { text: 'XAU',  dataIndex: 'xau', filter: {type: 'string'} , flex: 2 },
        { text: 'Amount',  dataIndex: 'xau', filter: {type: 'string'} , flex: 2 },
        { text: 'Remarks',  dataIndex: 'remarks', filter: {type: 'string'} , flex: 2 },
        { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 2 },
        { text: 'Cancel By', dataIndex: 'cancelby', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 2 },
        { text: 'Match Price ID',  dataIndex: 'matchpriceid', filter: {type: 'string'} , flex: 2 },
        { text: 'Match On', dataIndex: 'matchon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 2 },
        { text: 'Notify URL',  dataIndex: 'notifyurl', filter: {type: 'string'} , flex: 1 },
        { text: 'Notify Match URL',  dataIndex: 'notifymatchurl', filter: {type: 'string'} , flex: 1 },
        { text: 'Success Notify URL',  dataIndex: 'successnotifyurl', filter: {type: 'string'} , flex: 2 },
        { text: 'Reconciled',  dataIndex: 'reconciled', filter: {type: 'string'} , flex: 1 },
        { text: 'Reconciled On', dataIndex: 'reconciledon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 2 },
        { text: 'Reconciled By', dataIndex: 'reconciledby', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },



		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'  }, inputType: 'hidden', hidden: true, flex: 1 },
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        { text: 'System Status',  dataIndex: 'status',  flex: 2,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Pending'],
                       ['1', 'Active'],
                       ['2', 'Fulfilled'],
                       ['3', 'Matched'],
                       ['4', 'Pending Cancel'],
                       ['5', 'Cancelled'],
                       ['6', 'Expired'],

                   ],

               },
               renderer: function(value, rec){
                  if(value=='0') return 'Pending';
                  else if(value=='1') return 'Active';
                  else if(value=='2') return 'Fulfilled';
                  else if(value=='3') return 'Matched';
                  else if(value=='4') return 'Pending Cancel';
                  else if(value=='5') return 'Cancelled';
                  else return 'Expired';
              },
        }
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

    formConfig: {
        controller: 'orderqueue-orderqueue',
        formDialogTitle: 'Order Queue',

        // Form configuration

        formDialogWidth: 950,
            formDialogTitle: 'Order',
            enableFormDialogClosable: false,
            formPanelDefaults: {
                border: false,
                xtype: 'panel',
                flex: 1,
                layout: 'anchor',
                msgTarget: 'side',
               margins: '0 0 10 10'
            },
            enableFormPanelFrame: false,
            formPanelLayout: 'hbox',


        formPanelItems: [
        {  inputType: 'hidden', hidden: true, name: 'id' },
        {  xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },

        {
                    items:[
                        { xtype: 'hidden', hidden: true, name: 'id' },
                        { xtype: 'hidden', hidden: true, name: 'orderlist', bind: '{orderlist}' },
                        { xtype: 'hidden', hidden: true, name: 'orderdeliverydata',reference: 'orderdeliverydata'},
                        {
                            xtype: 'fieldset', title: 'Spot Order', collapsible: false,
                            default: { labelWidth: 90, layout: 'hbox'},
                            items: [
                                /*{ xtype: 'combobox',
                                    fieldLabel: 'Partner ID',
                                    name: 'partnerid',
                                    displayField: 'name',
                                    valueField: 'id',
                                    itemId: 'orderFromBranch',
                                    store: { type: 'Order', autoLoad: true, sorters: 'name'}, minChars: 0,
                                    listConfig: {
                                        getInnerTpl: function() {
                                            return '[ {code} ] {name}';
                                        }
                                    },
                                    displayTpl: Ext.create('Ext.XTemplate',
                                        '<tpl for=".">',
                                            '[ {code} ] {name}',
                                        '</tpl>'
                                    ),
                                    typeAhead: true,
                                    queryMode: 'local',
                                    forceSelection: true,
                                    listeners: {
                                        change: 'getBranchId'
                                    }
                                }, */
                        /*        { xtype: 'combobox',
                                    fieldLabel: 'Order Type',
                                    name: 'branchid',
                                    displayField: 'name',
                                    valueField: 'id',
                                    itemId: 'orderFromBranch',
                                    store: { type: 'Order', autoLoad: true, sorters: 'name'}, minChars: 0,
                                    listConfig: {
                                        getInnerTpl: function() {
                                            return '[ {code} ] {name}';
                                        }
                                    },
                                    displayTpl: Ext.create('Ext.XTemplate',
                                        '<tpl for=".">',
                                            '[ {code} ] {name}',
                                        '</tpl>'
                                    ),
                                    typeAhead: true,
                                    queryMode: 'local',
                                    forceSelection: true,
                                    listeners: {
                                        change: 'getBranchId'
                                    }
                                }, */
                                {
                                    xtype: 'combobox',
                                    fieldLabel: 'Product',
                                    name: 'productid',
                                    displayField: 'name',
                                    valueField: 'id',
                                    itemId: 'inventoryIdOrder',
                                    reference: 'inventorycatid',
                                    minChars: 0,
                                    store: {
                                        type: 'Order',
                                        //type: 'array',
                                        autoLoad: true,
                                        remoteFilter: true,
                                        sorters: 'name',
                                    },
                                    listConfig: {
                                        getInnerTpl: function() {
                                            return '[ {code} ] {name}';
                                        }
                                    },
                                    displayTpl: Ext.create('Ext.XTemplate',
                                        '<tpl for=".">',
                                            '[ {code} ] {name}',
                                        '</tpl>'
                                    ),
                                    typeAhead: true,
                                    queryMode: 'local',
                                    forceSelection: true,
                                },
                                // { xtype: 'combobox',
                                //     fieldLabel: 'Inventory',
                                //     name: 'inventorycatid',
                                //     displayField: 'name',
                                //     valueField: 'id',
                                //     itemId: 'inventoryIdOrder',
                                //     reference: 'inventorycatid',
                                //     store: { type: 'array', fields: ['id', 'name'], autoLoad: true, sorters: 'name'}, minChars: 0,
                                //     listConfig: {
                                //         getInnerTpl: function() {
                                //             return '[ {code} ] {name}';
                                //         }
                                //     },
                                //     displayTpl: Ext.create('Ext.XTemplate',
                                //         '<tpl for=".">',
                                //             '[ {code} ] {name}',
                                //         '</tpl>'
                                //     ),
                                //     typeAhead: true,
                                //     queryMode: 'local',
                                //     forceSelection: true,
                                //     listeners: {
                                //         change: 'getInventoryCat'
                                //     }
                                // },
                                // { xtype: 'combobox',
                                //     fieldLabel: 'Station',
                                //     name: 'stationid',
                                //     displayField: 'name',
                                //     valueField: 'id',
                                //     itemId: 'orderFromStation',
                                //     reference: 'stationid',
                                //     store: { type: 'array', fields: ['id', 'name'], autoLoad: true, sorters: 'name'}, minChars: 0,
                                //     listConfig: {
                                //         getInnerTpl: function() {
                                //             return '[ {id} ] {name}';
                                //         }
                                //     },
                                //     displayTpl: Ext.create('Ext.XTemplate',
                                //         '<tpl for=".">',
                                //             '[ {id} ] {name}',
                                //         '</tpl>'
                                //     ),
                                //     typeAhead: true,
                                //     queryMode: 'local',
                                //     forceSelection: true
                                // },



                            /*    {   xtype: 'combo',
                                    fieldLabel: 'Partnername',
                                    name: 'patientid',
                                    valueField: 'id',
                                    reference: 'patientlist',
                                    emptyText: 'Enter patient name for patient items order.',
                                    store: {
                                        type: 'array', fields: ['id', 'name'],
                                        sorters: 'name'
                                    },
                                    mode:'remote',
                                    minChars: 0,
                                    displayField: 'name',
                                    typeAhead: true,
                                    queryMode: 'local',
                                    hideTrigger:true,
                                    anchor: '100%',

                                    listConfig: {
                                        loadingText: 'Searching...',
                                        emptyText: 'No matching patient name found.',

                                        itemSelector: '.search-item',

                                        // Custom rendering template for each item
                                        itemTpl: [
                                            '<div data-qtip="{name}: {nric}">{name} ({nric})</div>'
                                        ]
                                    }
                                },*/
                                //{ xtype: 'textfield', fieldLabel: 'Partner Reference ID', name: 'ponum3', itemId: 'ponum3', emptyText: 'Enter Partner Reference ID for items order.'},
                                //{ xtype: 'textfield', fieldLabel: 'Is Spot', name: 'ponum4', itemId: 'ponum4'},
                                { xtype: 'displayfield', fieldLabel: 'Order On', itemId: 'orderon', value: new Date(),renderer: Ext.util.Format.dateRenderer('d-m-Y')},
                                { xtype: 'textfield', fieldLabel: 'Total Value (RM)', name: 'ponums', itemId: 'ponums'},
                                { xtype: 'textfield', fieldLabel: 'Total Xau Weight (gram)', name: 'ponum', itemId: 'ponum'},
                                //{ xtype: 'numberfield', fieldLabel: 'Total Ordered Amount', name: 'orderquantity'/*, minValue: 1, maxValue: 1000*/,width: 75},
                                //{ xtype: 'textareafield', fieldLabel: 'Remarks', name: 'remarks', itemId: 'remarks'}

                            ]
                        }
                    ],
                    listeners: {
                        afterRender : function(){
                            var me = this;
                            var permission = snap.getApplication().hasPermission('/root/branch/order/approve');
                            var fieldBranch = Ext.ComponentQuery.query('#orderFromBranch');
                            var fieldPO = Ext.ComponentQuery.query('#ponum');
                            if(permission !== true) {
                                fieldBranch[0].disable();
                                fieldPO[0].disable();
                            }
                        }
                    }
                },
        //Display all category from Tag table
       /* {   xtype: 'combobox', fieldLabel: 'Category', name: 'categoryid', displayField: 'code', width: '100%', valueField: 'id', reference: 'categoryid',  minChars: 0, typeAhead: true,
            store: { type: 'Tag', sorters: 'code', autoLoad: true },
			listConfig: {
                itemTpl: [
                    '<div data-qtip="{code}: {id}">{code}, {id}</div>'
                ]
            }
        },*/

        //Display only 'Trading Schedule' category from Tag table
        /*{   xtype: 'combobox', fieldLabel:'Category',  name: 'categoryid', valueField: 'id', displayField: 'code',  reference: 'categoryid', queryMode: 'local', forceSelection: true, editable: false,
            store: {
                type: 'Tag',
                autoLoad: true,
                filterOnLoad: true,
                remoteFilter: true,
                sorters: 'category',
                filters: [{ property: 'category', value: 'TradingSchedule', exactMatch: true,   caseSensitive: true }],
            },
        },

        {   xtype: 'combobox', fieldLabel:'Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type',    valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },

        {   xtype: 'datefield', fieldLabel: 'Start At', name: 'startat',  format: 'Y-m-d H:i:s'},
        {   xtype: 'datefield', fieldLabel: 'End At', name: 'endat',  format: 'Y-m-d H:i:s'},*/
        ]
    },

});
