Ext.define('snap.view.order.Order', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'orderview',

    requires: [

        'snap.store.Order',
        'snap.model.Order',
        'snap.view.order.OrderController',
        'snap.view.order.OrderModel',


    ],
    formDialogWidth: 950,
    //permissionRoot: '/root/gtp/order',
    store: { type: 'Order' },
    controller: 'order-order',
    viewModel: {
        type: 'order-order'
    },
    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', style : "width : 110px;",  text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-times-circle', style : "width : 110px;",  text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
        {
            style : "width : 110px;", text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
        },
        {
            text: 'Export Zip', tooltip: 'Export Zip To Email', iconCls: 'x-fa fa-envelope', handler: 'getPrintReportJob',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {reference: 'receiptButton', text: 'Receipt', style : "width : 110px;", itemId: 'receiptButton', tooltip: 'Print Receipt', iconCls: 'x-fa fa-file', handler: 'printReceipt', showToolbarItemText: true, labelWidth:'auto', validSelection: 'single' },
        {reference: 'cancelOrder', style : "width : 110px;",  text: 'Cancel', itemId: 'cancelOrd', tooltip: 'Cancel Order', iconCls: 'x-fa fa-times', handler: 'cancelOrders', showToolbarItemText: true, validSelection: 'single', labelWidth:'auto' },
        {reference: 'sendButton', style : "width : 110px;",  text: 'Send SAP', itemId: 'sendToSap', tooltip: 'Send order to SAP', iconCls: 'x-fa fa-envelope', handler: 'sendToSAP', showToolbarItemText: true, validSelection: 'multiple', labelWidth:'auto'},
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
            if(this.lookupReference('sendButton')){
                sendButton = this.lookupReference('sendButton');
                sendButton.setHidden(true);
    
                // Check for type 
                // By right is only for trader
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    
                    // Do permission check for said user roles
                    var sendPermission = snap.getApplication().hasPermission('/root/gtp/order/submit');
                    if(sendPermission == true){
                        sendButton.setHidden(false);
                    }
                } 
            }

            if(this.lookupReference('cancelOrder')){
                cancelOrderButton = this.lookupReference('cancelOrder');
                cancelOrderButton.setHidden(true);
    
                // Check for type 
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    
                    // Do permission check for said user roles
                    var cancelPermission = snap.getApplication().hasPermission('/root/gtp/order/cancel');
                    if(cancelPermission == true){
                        cancelOrderButton.setHidden(false);
                    }
                    
                    
                } 
            }
            
            /*
            snap.getApplication().sendRequest({
                hdl: 'order', action: 'isHideSendToSap'
                }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        if(!data.hide){
       
                            button.setHidden(false);
                        }
                        
                        //Ext.get('allocatedcount').dom.innerHTML = data.allocatedcount;
                        //Ext.getCmp('allocatedcount').setValue(data.allocatedcount);
                        //Ext.get('availablecount').dom.innerHTML = data.availablecount;
                        //Ext.get('onrequestcount').dom.innerHTML = data.onrequestcount;
                        //Ext.get('returncount').dom.innerHTML = data.returncount;                      
                    }
            })*/
        }
    },

    columns: [
        { text: 'Booking On', dataIndex: 'bookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 200, },
        //{ text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },
        { text: 'Order No.', dataIndex: 'orderno', filter: { type: 'string' }, minWidth: 110,
            renderer: 'ordernoColor'
        },
        { text: 'Product', dataIndex: 'productname', filter: { type: 'string' }, minWidth: 130 },
        {
            text: 'Ace Buy/Sell', dataIndex: 'type',
            filter: {
                type: 'combo',
                store: [
                    ['CompanySell', 'CompanySell'],
                    ['CompanyBuy', 'CompanyBuy'],
                    ['CompanyBuyBack', 'CompanyBuyBack'],
                ],
                renderer: function (value, rec) {
                    if (value == 'CompanySell') return 'CompanySell';
                    else if (value == 'CompanyBuy') return 'CompanyBuy';
                    else return 'CompanyBuyBack';
                },
            },

        },
        { text: 'Price Provider', dataIndex: 'priceprovidername', filter: { type: 'string' }, minWidth: 200, },
        { text: 'Xau Weight (g)', dataIndex: 'xau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        {
            text: 'GP', dataIndex: 'price', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, 
            xtype: 'numbercolumn', format: '0,000.000'
    },
        { text: 'Premium/Refine Fee',  dataIndex: 'fee', exportdecimal:2, filter: {type: 'string'}, flex: 1, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'FP', dataIndex: 'fpprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, 
            xtype: 'numbercolumn', format: '0,000.000'
        },
      
        // { text: 'Fee Type',  dataIndex: 'feetypename', filter: {type: 'string'}, flex: 1 },
        {
            text: 'P2 Price', dataIndex: 'bookingprice', hidden: true, exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        {
            text: 'Total Amount (RM)', dataIndex: 'amount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        {
            text: 'Discount', dataIndex: 'discountprice', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount Info', dataIndex: 'discountinfo', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
        },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Confirmed'],
                    ['2', 'PendingPayment'],
                    ['3', 'PendingCancel'],
                    ['4', 'Reversal'],
                    ['5', 'Completed'],
                    ['6', 'Cancelled'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span data-qtitle="Pending" data-qwidth="200" '+
                'data-qtip="Order Received">'+
                 "Pending" +'</span>';
                else if (value == '1') return '<span data-qtitle="Confirmed" data-qwidth="200" '+
                'data-qtip="After push to SAP">'+
                 "Confirmed" +'</span>';
                else if (value == '2') return '<span data-qtitle="Pending Payment" data-qwidth="200" '+
                'data-qtip="Temporary status not in use">'+
                 "Pending Payment" +'</span>';
                else if (value == '3') return '<span data-qtitle="Pending Cancel" data-qwidth="200" '+
                'data-qtip="Maybank request cancel before the end of the day">'+
                 "Pending Cancel" +'</span>';
                else if (value == '4') return '<span data-qtitle="Reversal" data-qwidth="200" '+
                'data-qtip="Direct cancelled">'+
                 "Reversal" +'</span>';
                else if (value == '5') return '<span data-qtitle="Completed" data-qwidth="200" '+
                'data-qtip="Acknowledged by SAP">'+
                 "Completed" +'</span>';
                else return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                'data-qtip="Cancelled either by ACE, GTP or SAP">'+
                 "Cancelled" +'</span>';
            },
        },
        { 
            text: 'Partner Ref', dataIndex: 'partnerrefid', hidden: true, filter: { type: 'string' }, minWidth: 130,
            renderer: 'boldText' 
        },
        { text: 'Partner Code', dataIndex: 'partnercode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        // { text: 'Partner Buy Code', dataIndex: 'partnerbuycode1', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        // { text: 'Partner Sell Code', dataIndex: 'partnersellcode1', hidden: true, filter: { type: 'string' }, minWidth: 130 },
      
        //{ text: 'Price Stream ID', dataIndex: 'pricestreamid', filter: {type: 'string'}, hidden: true, flex: 2,},
        { text: 'Assigned Salesperson', dataIndex: 'salespersonname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Order Made By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        //{ text: 'Api Version',  dataIndex: 'apiversion', filter: {type: 'string'}, flex: 1 },




        //{ text: 'Is Spot',  dataIndex: 'isspot', hidden:true, filter: {type: 'int'}, flex: 1 },
        /*{ text: 'Is Spot',  dataIndex: 'isspot',  flex: 1,

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
        },*/
     
        {
            text: 'Price Validation ID', dataIndex: 'uuid', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
        },
        {
            text: 'Is Spot', dataIndex: 'isspot', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Future'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'Future';
            },
        },
        //{ text: 'By Weight', dataIndex: 'byweight', filter: { type: 'string' }, hidden: true, minWidth: 80 },
        {
            text: 'Book By', dataIndex: 'byweight', minWidth: 80,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Amount'],
                    ['1', 'Weight'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span style="color:#800080;">' + 'Amount' + '</span>';
                else if (value == '1') return '<span style="color:#d4af37;">' + 'Weight' + '</span>';
                else return 'Unassigned';
            },
        },
        
        { text: 'Product Code', dataIndex: 'productcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
     

      

   
        // { text: 'SAP DOC NUMBER', dataIndex: 'reconciledsaprefno',  align: 'right', filter: { type: 'string' }, minWidth: 130 },
     
        /*
        { text: 'Remarks', dataIndex: 'remarks', hidden:true, filter: {type: 'string'}, hidden: true },
       
        { text: 'Booking Price', dataIndex: 'bookingprice', filter: {type: 'string'}, flex: 1 },
        { text: 'Booking Price Stream ID' , dataIndex: 'bookingpricestreamid', hidden:true, filter: {type: 'string'}, hidden: true },

        { text: 'Confirm On', dataIndex: 'confirmon',  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Confirm By', dataIndex: 'confirmbyname', filter: {type: 'string'}, flex: 1 },
        { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', filter: {type: 'string'}, hidden: true },
        { text: 'Confirm Price', dataIndex: 'confirmprice',  filter: {type: 'string'}, flex: 1, },
        { text: 'Confirm Reference', dataIndex: 'confirmreference',  filter: {type: 'string'}, hidden: true },

        { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Cancel By', dataIndex: 'cancelbyname', filter: {type: 'string'}, flex: 1, },
        { text: 'Cancel Price Stream ID', dataIndex: 'cancelpricestreamid', filter: {type: 'string'}, flex: 1 },
        { text: 'Cancel Price', dataIndex: 'cancelprice',  filter: {type: 'string'}, flex: 1, },
        { text: 'Notify URL', dataIndex: 'notifyurl',  hidden:true, filter: {type: 'string'}, hidden: true },
        
        //{ text: 'Reconciled', dataIndex: 'reconciled',  hidden:true, filter: {type: 'string'}, flex: 1, },
        { text: 'Reconciled',  dataIndex: 'reconciled',  flex: 1,

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
        { text: 'Reconciled On', dataIndex: 'reconciledon', hidden:true,  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Reconciled By',  dataIndex: 'reconciledbyname', hidden:true, filter: {type: 'string'}, flex: 1 },
        */
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
       
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        
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
            { inputType: 'hidden', hidden: true, name: 'id' },
            { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },

            {
                items: [
                    { xtype: 'hidden', hidden: true, name: 'id' },
                    { xtype: 'hidden', hidden: true, name: 'orderlist', bind: '{orderlist}' },
                    { xtype: 'hidden', hidden: true, name: 'orderdeliverydata', reference: 'orderdeliverydata' },
                    {
                        xtype: 'fieldset', title: 'Spot Order', collapsible: false,
                        default: { labelWidth: 90, layout: 'hbox' },
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
                                    getInnerTpl: function () {
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
                            { xtype: 'displayfield', fieldLabel: 'Order On', itemId: 'orderon', value: new Date(), renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s') },
                            { xtype: 'textfield', fieldLabel: 'Total Value (RM)', name: 'ponums', itemId: 'ponums' },
                            { xtype: 'textfield', fieldLabel: 'Total Xau Weight (gram)', name: 'ponum', itemId: 'ponum' },
                            //{ xtype: 'numberfield', fieldLabel: 'Total Ordered Amount', name: 'orderquantity'/*, minValue: 1, maxValue: 1000*/,width: 75},
                            //{ xtype: 'textareafield', fieldLabel: 'Remarks', name: 'remarks', itemId: 'remarks'}

                        ]
                    }
                ],
                listeners: {
                    afterRender: function () {
                        var me = this;
                        var permission = snap.getApplication().hasPermission('/root/branch/order/approve');
                        var fieldBranch = Ext.ComponentQuery.query('#orderFromBranch');
                        var fieldPO = Ext.ComponentQuery.query('#ponum');
                        if (permission !== true) {
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



    /******************************************************** Form for send to SAP ******************************************* */
    formSap: {
        reference: 'ordersapformrecord',
        controller: 'order-order',
        formDialogTitle: 'Sales Order Handling',
        viewModel: {
            data: {
            }
        },

        //formDialogWidth: 1150,
        formDialogWidth: 750,
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 300,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {

                title: 'Send to Sap ...',
                layout: 'fit',
                width: 850,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                frame: true,
                layout: 'column',
                defaults: {
                    columnWidth: .5,                
                },    
                reference: 'orderhandling-confirmation',     
                border: 0,
                bodyBorder: false,
                bodyPadding: 10,
                items: [
                    {
                        columnWidth: 0.48,
                        items: [
                            { xtype: 'hidden', hidden: true, name: 'id' },
                            { xtype: 'displayfield', fieldLabel:  'GTP Ref#',  name: 'GTP Ref#', reference: 'gtpref', },
                            { xtype: 'displayfield', fieldLabel: 'Booking Number',  name: 'Booking Number', reference: 'bookingnumber',},
                            { xtype: 'displayfield', fieldLabel: 'XAU Weight (g)', name: 'XAU Weight (g)', reference: 'xauweight', },
                            { xtype: 'displayfield', fieldLabel: 'Price (RM/g)', name: 'Price (RM/g)', reference: 'price', },
                            { xtype: 'displayfield', name: 'buyorsell', reference: 'buyorsell',},
                            { xtype: 'displayfield', fieldLabel: 'Product Type', name: 'productname', reference: 'productname',},
                        
                        
                        ]
                    },
                    {
                        columnWidth: 0.48,
                        items: [
                            { xtype: 'displayfield', fieldLabel:  'Status',  name: 'status', reference: 'status'},
                            { xtype: 'textarea', fieldLabel: 'Remarks',  name: 'remarks', },
                            { xtype: 'displayfield', fieldLabel: 'Gross Value (RM)',  name: 'grossvalue', reference: 'grossvalue' },
                            { xtype: 'displayfield', fieldLabel: 'Contact Phone',name: 'contactno', },
                            { xtype: 'displayfield', hidden: true,  fieldLabel: 'Partner Reference', name: 'partnerreference', reference: 'partnerreference',},
                            { xtype: 'displayfield', hidden: true,  fieldLabel: 'companybuyorsell', name: 'companybuyorsell', reference: 'companybuyorsell',},
                            { xtype: 'displayfield', hidden: true,  fieldLabel: 'product', name: 'product', reference: 'product',},
                        
                        
                        ]
                    },
                    
                ]	
            }
        ]
    },
    formSendToSap: {
        reference: 'ordersapformrecord',
        controller: 'order-order',
        formDialogTitle: 'Serial Number to SAP',
        viewModel: {
            data: {
            }
        },

        //formDialogWidth: 1150,
        formDialogWidth: 1250,
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 300,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {

                //title: 'Send To Sap',
                //iconCls: 'x-fa fa-calendar',
                xtype: 'form',
                id: 'sendtosapformdisplay',
                //reference: 'userdailylimit',
                //store: { type: 'Partner' },
                /*viewModel: {
                    type: 'partner-partner'
                },
                header: {
                    // Custom style for Migasit
                    /*style: {
                        backgroundColor: '#204A6D',
                    },
                    style : 'background-color: #204A6D;border-color: #204A6D;',
                },*/
                scrollable: true,
                items: [
                   
                    
                ]	
            }
        ]
    }


});
