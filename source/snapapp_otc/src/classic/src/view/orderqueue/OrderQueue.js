Ext.define('snap.view.orderqueue.OrderQueue',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'orderqueueview',

    requires: [
        'snap.store.OrderQueue',
        'snap.model.OrderQueue',
        'snap.view.orderqueue.OrderQueueController',
        'snap.view.orderqueue.OrderQueueModel'
    ],
    permissionRoot: '/root/gtp/ftrorder',
    store: { type: 'OrderQueue' },
    controller: 'orderqueue-orderqueue',

    viewModel: {
        type: 'orderqueue-orderqueue'
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
        {reference: 'cancelOrder', text: 'Cancel Order', itemId: 'cancelOrd', tooltip: 'Cancel Future Order', iconCls: 'x-fa fa-times', handler: 'cancelOrders', validSelection: 'single' }
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

            // if(this.lookupReference('cancelOrder')){
            //     cancelButton = this.lookupReference('cancelOrder');
            //     cancelButton.setHidden(true);
    
            //     // Check for type 
            //     if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
            //         cancelButton.setHidden(false);
            //     } 
            // }
            
            
        }
    },
    columns: [
        { text: 'Status',  dataIndex: 'status', minWidth:100,

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
        },
        { text: 'ID',  dataIndex: 'id', filter: {type: 'string'}, hidden: true, flex: 1 },
        //{ text: 'Order ID',  dataIndex: 'orderid', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Spot Order No',  dataIndex: 'orderid', filter: {type: 'string'} ,hidden: true,minWidth:130},
        { text: 'Order Queue No',  dataIndex: 'orderqueueno', filter: {type: 'string'} ,hidden: true,minWidth:130, 
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.htmlEncode(value)
            },
        },
        { text: 'Partner Name',  dataIndex: 'partnername', hidden: true, filter: {type: 'string'}, flex: 1 },
       
        //{ text: 'Buyer Name',  dataIndex: 'buyername', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Partner Ref No.',  dataIndex: 'partnerrefid', filter: {type: 'string'} ,hidden: true, flex: 1 , renderer: 'boldText'},
        { text: 'Salesperson Name',  dataIndex: 'salespersonname', filter: {type: 'string'} , hidden: true,minWidth:100},
        //{ text: 'API Version',  dataIndex: 'apiversion', filter: {type: 'string'} ,  flex: 1d },
        { text: 'Order Type',  dataIndex: 'ordertype', minWidth:100,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
        //{ text: 'Queue Type',  dataIndex: 'queuetype', filter: {type: 'string'  } , flex: 1, hidden: true },
        //{ text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
       
        { text: 'Price Target (RM/g)',  dataIndex: 'pricetarget', filter: {type: 'string'} ,minWidth:140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        //{ text: 'By Weight',  dataIndex: 'byweight', filter: {type: 'string'} , flex: 1 },
        { text: 'Book By',  dataIndex: 'byweight',  minWidth:100,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Amount'],
                       ['1', 'Weight'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return '<span style="color:#800080;">' + 'Amount' + '</span>';
                   else if(value=='1') return '<span style="color:#d4af37;">' + 'Weight' + '</span>';
                   else return 'Unassigned';
              },
        },
        { text: 'Xau Weight (g)',  dataIndex: 'xau', filter: {type: 'string'} ,minWidth:130, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  
        },
        { text: 'Amount (RM)',  dataIndex: 'amount', filter: {type: 'string'} ,minWidth:100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        { text: 'Product',  dataIndex: 'productname', filter: {type: 'string'} ,minWidth:130  },
        { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, minWidth:100 },
        //{ text: 'Effective On', dataIndex: 'effectiveon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, minWidth:100 },
        
        // { text: 'Remarks',  dataIndex: 'remarks', filter: {type: 'string'} , flex: 1 },
        // { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        // { text: 'Cancel By', dataIndex: 'cancelbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        // { text: 'Match Price ID',  dataIndex: 'matchpriceid', filter: {type: 'string'} , flex: 1 },
        // { text: 'Match Price (RM)',  dataIndex: 'companybuyppg', filter: {type: 'string'} ,minWidth:100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        // editor: {    //field has been deprecated as of 4.0.5
        //     xtype: 'numberfield',
        //     decimalPrecision: 3
        // } }, 
        { text: 'Matched On', dataIndex: 'matchon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'},  flex: 1 },
        // { text: 'Notify URL',  dataIndex: 'notifyurl', filter: {type: 'string'} , flex: 1 },
        // { text: 'Notify Match URL',  dataIndex: 'notifymatchurl', filter: {type: 'string'} , hidden: true, flex: 1 },
        // { text: 'Success Notify URL',  dataIndex: 'successnotifyurl', filter: {type: 'string'} , hidden: true, flex: 1 },
        // { text: 'Reconciled',  dataIndex: 'reconciled', filter: {type: 'int'} , flex: 1 },
        // { text: 'Reconciled On', dataIndex: 'reconciledon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        // { text: 'Reconciled By', dataIndex: 'reconciledbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        


		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true,minWidth:100  },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, minWidth:100},
       
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
