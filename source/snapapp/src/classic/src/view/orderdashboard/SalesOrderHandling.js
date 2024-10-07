Ext.define('snap.view.orderdashboard.SalesOrderHandling',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'salesorderhandlingview',

    requires: [

        'snap.store.Order',
        'snap.model.Order',
        'snap.view.orderdashboard.SalesOrderHandlingController',
        'snap.view.order.OrderModel',


    ],
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    store: { type: 'Order' },
    controller: 'salesorderhandling-salesorderhandling',
    viewModel: {
        type: 'order-order'
    },
    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter',
        {reference: 'sendButton', text: 'Send to SAP', itemId: 'sendToSap', tooltip: 'Send order to SAP', iconCls: 'x-fa fa-envelope', handler: 'sendToSAP', validSelection: 'single'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
    ],

    columns: [
       
        { text: 'Booking #', dataIndex: 'orderno', filter: {type: 'string'}, },
        { text: 'GTP Doc#', dataIndex: 'id', filter: {type: 'int'}, flex: 1 },
        { text: 'Price (RM/g)', dataIndex: 'price', filter: {type: 'string'}, flex: 1, renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        { text: 'Book By',  dataIndex: 'byweight',  flex: 1,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Amount'],
                       ['1', 'Weight'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'Amount';
                   else if(value=='1') return 'Weight';
                   else return 'Unassigned';
              },
        },
        { text: 'Xau Weight (g)', dataIndex: 'xau', filter: {type: 'string'}, },
        { text: 'Partner',  dataIndex: 'partnername', filter: {type: 'string'}, flex: 1, hidden: true},
        { text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },
        { text: 'Product Type',  dataIndex: 'productname', filter: {type: 'string'}, flex: 1 },
        { text: 'Ace Buy/ Sell',  dataIndex: 'type', flex: 1 ,
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
        { text: 'Status',  dataIndex: 'status',  flex: 2,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Pending'],
                       ['1', 'Confirmed'],
                       ['2', 'PendingPayment'],
                       ['3', 'PendingCancel'],
                       ['4', 'Cancelled'],
                       ['5', 'Completed'],
                       ['6', 'Expired'],

                   ],

               },
               renderer: function(value, rec){
                  if(value=='0') return 'Pending';
                  else if(value=='1') return 'Confirmed';
                  else if(value=='2') return 'PendingPayment';
                  else if(value=='3') return 'PendingCancel';
                  else if(value=='4') return 'Cancelled';
                  else if(value=='5') return 'Completed';
                  else return 'Expired';
              },
        },

        { text: 'Product Code', dataIndex: 'productcode', hidden: true, filter: {type: 'string'}, width: 150 },
        { text: 'Partner Ref', dataIndex: 'partnerrefid', hidden:true, filter: {type: 'string'}, flex: 1 },
       
        { text: 'Price Stream ID', dataIndex: 'pricestreamid', filter: {type: 'string'}, hidden: true, flex: 2,},
        { text: 'Salesperson Name',  dataIndex: 'salespersonname', filter: {type: 'string'} , hidden: true, flex: 1 },
        //{ text: 'Api Version',  dataIndex: 'apiversion', hidden: true, filter: {type: 'string'}, flex: 1 },

       


       
        //{ text: 'Is Spot',  dataIndex: 'isspot', hidden:true, filter: {type: 'int'}, flex: 1 },
        { text: 'Is Spot', hidden: true, dataIndex: 'isspot',  flex: 1,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'False'],
                       ['1', 'True'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'False';
                   else if(value=='1') return 'True';
                   else return 'Unassigned';
              },
        },
        
        //{ text: 'By Weight', dataIndex: 'byweight', filter: {type: 'string'}, hidden: true },
        
       

        { text: 'Amount',  dataIndex: 'amount', hidden: true, filter: {type: 'string'}, flex: 1, renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        //{ text: 'Fee',  dataIndex: 'fee', hidden: true,filter: {type: 'string'}, flex: 1 },

        { text: 'Remarks', dataIndex: 'remarks', hidden:true, filter: {type: 'string'}, hidden: true },
        { text: 'Booking On', dataIndex: 'bookingon',hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
        { text: 'Booking Price', dataIndex: 'bookingprice', hidden: true, filter: {type: 'string'}, flex: 1 },
        { text: 'Booking Price Stream ID' , dataIndex: 'bookingpricestreamid',  filter: {type: 'string'}, hidden: true },
        /*
        { text: 'Confirm On', dataIndex: 'confirmon', hidden: true, xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Confirm By', dataIndex: 'confirmbyname', hidden: true,filter: {type: 'string'}, flex: 1 },
        { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', filter: {type: 'string'}, hidden: true },
        { text: 'Confirm Price', dataIndex: 'confirmprice',  filter: {type: 'string'}, flex: 1, },
        { text: 'Confirm Reference', dataIndex: 'confirmreference',  filter: {type: 'string'}, hidden: true },

        { text: 'Cancel On', dataIndex: 'cancelon', hidden: true,xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Cancel By', dataIndex: 'cancelbyname',hidden: true, filter: {type: 'string'}, flex: 1, },
        { text: 'Cancel Price Stream ID', hidden: true,  dataIndex: 'cancelpricestreamid', filter: {type: 'string'}, flex: 1 },
        { text: 'Cancel Price', dataIndex: 'cancelprice',  hidden: true, filter: {type: 'string'}, flex: 1, },
        { text: 'Notify URL', dataIndex: 'notifyurl',  hidden:true, filter: {type: 'string'}, hidden: true },
        */
        //{ text: 'Reconciled', dataIndex: 'reconciled',  hidden:true, filter: {type: 'string'}, flex: 1, },
        { text: 'Reconciled', hidden: true, dataIndex: 'reconciled',  flex: 1,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'False'],
                       ['1', 'True'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'False';
                   else if(value=='1') return 'True';
                   else return 'Unassigned';
              },
        },
        { text: 'Reconciled On', dataIndex: 'reconciledon', hidden:true,  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
        { text: 'Reconciled By',  dataIndex: 'reconciledbyname', hidden:true, filter: {type: 'string'}, flex: 1 },

        { text: 'Created On', dataIndex: 'createdon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1},
        { text: 'Modified On', dataIndex: 'modifiedon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'}, hidden: true },
        
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
                                { xtype: 'displayfield', fieldLabel: 'Order On', itemId: 'orderon', value: new Date(),renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                                { xtype: 'textfield', fieldLabel: 'Total Value (RM)', name: 'ponums', itemId: 'ponums',  renderer:  Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }},
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

/*

    //grouped: true,
                                selType: 'rowmodel',

                                plugins: [
                                    //{   ptype: 'rowediting', id: 'rowEditPlugin', clicksToMoveEditor: 1, autoCancel: false },
                                    {   ptype: 'rowexpander', //rowexpander: true, //ptype: 'rowexpandergrid',
                                        expandOnDblClick: false,
                                        selectRowOnExpand: false,

                                        // store: {
                                        //     type: 'array' ,
                                        //     fields: ['servedbyname', 'servedon', 'quantity']
                                        // },

                                        rowBodyTpl: Ext.create('Ext.XTemplate',
                                        '<table>',
                                            '<tr>',
                                                '<th style="text-align:center; width:200px">Cancel Price Id</th>',
                                                '<th style="text-align:center; width:200px">Cancel Price</th>',
                                                '<th style="text-align:center; width:200px">Notify Url</th>',
                                                '<th style="text-align:center; width:200px">Reconciled</th>',
                                                '<th style="text-align:center; width:200px">Reconciled on</th>',
                                                '<th style="text-align:center; width:200px">Reconciled by</th>',
                                            '</tr>',
                                            '{id}',
                                            // '<tr>',
                                            //     '<td style="width:130px">a</td>',
                                            //     '<td style="width:130px">b</td>',
                                            //     '<td style="width:130px">c</td>',
                                            // '</tr>',
                                        '</table>'
                                        )
                                        // rowBodyTpl: [
                                        //     '<table>',
                                        //         '<tr>',
                                        //                 '<td>  Served on: {servedon} </td>',

                                        //                 '<td>  Served by: {servedbyname} </td>',

                                        //                 '<td>  Quantity: {quantity} </td>',
                                        //         '</tr>',
                                        //     '</table>',

                                        // ],
                                    }
                                ],
    formClass: 'snap.view.order.OrderGridForm' */

     //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Send to SAP
    //
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    /*--------------------------------------------------------- Send Order To SAP -----------------------------------------------------------------------*/

    formSendToSAP: {
        controller: 'salesorderhandling-salesorderhandling',

        formDialogWidth: 950,

        formDialogTitle: 'Sales Order Handling',

        // Settings
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
            formViewModel: {

            },

        formPanelItems: [
                //1st hbox
                {
                    //title: 'Partner Details',
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: 550,
                    //height: 300,
                    //default: { labelWidth: 70},
                    items: [
                        {
                            columnWidth: 0.48,
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                { xtype: 'displayfield', fieldLabel:  'GTP Ref#', name: 'GTP Ref#' },
                                { xtype: 'displayfield', fieldLabel: 'Booking Number', name: 'Booking Number', },
                                { xtype: 'displayfield', fieldLabel: 'XAU Weight (g)', name: 'XAU Weight (g)', },
                                { xtype: 'displayfield', fieldLabel: 'Price (RM/g)', name: 'Price (RM/g)', },
                                { xtype: 'displayfield', fieldLabel: 'Buy From Customer', name: 'Buy From Customer', },
                                { xtype: 'displayfield', fieldLabel: 'Product Type', name: 'productname', },
                               
                              
                            ]
                        },
                        {
                            columnWidth: 0.48,
                            items: [
                                { xtype: 'displayfield', fieldLabel:  'Status', name: 'status' },
                                { xtype: 'textarea', fieldLabel: 'Remarks', name: 'remarks', },
                                { xtype: 'displayfield', fieldLabel: 'Gross Value (RM)', name: 'grossvalue', },
                                { xtype: 'displayfield', fieldLabel: 'Contact Phone', name: 'contactno', },
                               
                              
                            ]
                        },
                        
                    ]
                },
                //2nd hbox


            ]
    },

});
