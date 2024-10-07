
Ext.define('snap.view.redemption.MyConversionRequestsPkbAffi', {
    extend: 'snap.view.redemption.RedemptionRequests',
    xtype: 'myconversionrequestspkbaffi',
    requires: [
        'snap.store.MyConversion',
        'snap.model.MyConversion',
        'snap.store.SalesPersons',
        'snap.model.SalesPersons',
        'snap.view.redemption.MyConversionController',
        'snap.view.redemption.RedemptionModel',
    ],
    //permissionRoot: '/root/bmmb/redemption',
    store: { type: 'MyConversion' },
    controller: 'myconversion-myconversion',
    detailViewWindowHeight: 400,
    gridSelectionMode: 'SINGLE',
    allowDeselect:true,
    height: '85%',
    enableFilter: true,
    rowexpander: {
        ptype: 'rowexpander', //rowexpander: true, //ptype: 'rowexpandergrid', 
        pluginId: 'rowexpander',
        expandOnDblClick: true,
        selectRowOnExpand: false,
        expandRow: function(rowIdx) {
            var rowNode = this.view.getNode(rowIdx),
                row = Ext.get(rowNode),
                nextBd = Ext.get(row).down(this.rowBodyTrSelector),
                record = this.view.getRecord(rowNode),
                grid = this.getCmp();
            if (row.hasCls(this.rowCollapsedCls)) {
                row.removeCls(this.rowCollapsedCls);
                nextBd.removeCls(this.rowBodyHiddenCls);
                this.recordsExpanded[record.internalId] = true;
                this.view.fireEvent('expandbody', rowNode, record, nextBd.dom);
            }
        },
    
        collapseRow: function(rowIdx) {
            var rowNode = this.view.getNode(rowIdx),
                row = Ext.get(rowNode),
                nextBd = Ext.get(row).down(this.rowBodyTrSelector),
                record = this.view.getRecord(rowNode),
                grid = this.getCmp();
            if (!row.hasCls(this.rowCollapsedCls)) {
                row.addCls(this.rowCollapsedCls);
                nextBd.addCls(this.rowBodyHiddenCls);
                this.recordsExpanded[record.internalId] = false;
                this.view.fireEvent('collapsebody', rowNode, record, nextBd.dom);
            }
        },

        rowBodyTpl: Ext.create('Ext.XTemplate',
        '<table style="width: 400px;border: 1px solid black;margin-bottom:15px;">',
            '<tr>',
                '<th style="text-align:center; width:200px">Serial No.</th>',
                '<th style="text-align:center; width:200px">Gold Code</th>',
            '</tr>',
            '{child}',
        '</table>'
        )                                      
    },
    
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter','|',
        { reference: 'sendButton', text: 'Send', itemId: 'sendToLogistics', tooltip: 'Send to Logistics', iconCls: 'x-fa fa-paper-plane', handler: 'scheduleDate', validSelection: 'single' },

        '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            text: 'Print',tooltip: 'Print Daily Conversion Report',iconCls: 'x-fa fa-print', reference: 'dailyconversionreport', handler: 'getConversionReportPkbAffi',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
       
        // {reference: 'summaryButton', text: 'Summary', itemId: 'summaryOfRedemption', tooltip: 'Summary', iconCls: 'x-fa fa-list-alt', handler: 'summaryOfRedemption', validSelection: 'single' }
    ],   
    columns: [
        { text: 'ID', dataIndex: 'id', renderer: 'setTextColor' ,hidden:true,filter: { type: 'int' }},
        { text: 'Redemption ID', dataIndex: 'redemptionid', renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Customer Code', dataIndex: 'accountholdercode', renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Customer Name', dataIndex: 'accountholdername', renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Conversion Type', dataIndex: 'rdmtype', renderer: 'setTextColor',filter: { type: 'string' },minWidth:130 },
        { text: 'Payment Status',  dataIndex: 'status', minWidth:130, renderer: 'setTextColor',

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Pending Payment'],
                       ['1', 'Paid'],
                       ['2', 'Expired'],
                       ['3', 'Payment Cancelled'],
                       ['4', 'Reversed'],
                   ],
               },
               renderer: function(value, rec){
                   if (value == 0) return '<span data-qtitle="Pending" data-qwidth="200" '+
                   'data-qtip="Conversion is pending for payment">'+
                   "Pending" +'</span>';
                   if (value == 1) return '<span data-qtitle="Paid" data-qwidth="200" '+
                   'data-qtip="Conversion has been paid">'+
                   "Paid" +'</span>';
                   if (value == 2) return '<span data-qtitle="Expired" data-qwidth="200" '+
                   'data-qtip="Conversion has expired">'+
                   "Expired" +'</span>';
                    if (value == 3) return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                    'data-qtip="Conversion was cancelled by merchant">'+
                        "Cancelled" + '</span>';
                    if (value == 4) return '<span data-qtitle="Reversed" data-qwidth="200" '+
                    'data-qtip="Conversion was reversed">'+
                    "Cancelled" +'</span>';
                    else return '';
              },
        },
        { text: 'Conversion Status',  dataIndex: 'rdmstatus', minWidth:130, renderer: 'setTextColor',

                filter: {
                    type: 'combo',
                    store: [
                        ['0', 'Pending'],
                        ['1', 'Confirmed'],
                        ['2', 'Completed'],
                        ['3', 'Failed'],
                        ['4', 'Process Delivery'],
                        ['5', 'Cancelled'],
                        ['6', 'Reversed'],
                        ['7', 'Failed Delivery'],
                        //['Collecting', 10],

                    ],

                },
                renderer: function(value, rec){
                    if (value == 0) return '<span data-qtitle="Pending" data-qwidth="200" '+
                    'data-qtip="Redemption is pending for the next action">'+
                    "Pending" +'</span>';
                    if (value == 1) return '<span data-qtitle="Confirmed" data-qwidth="200" '+
                    'data-qtip="Redemption request is confirmed for delivery">'+
                    "Confirmed" +'</span>';
                    if (value == 2) return '<span data-qtitle="Completed" data-qwidth="200" '+
                    'data-qtip="Redemption is successful or delivered">'+
                    "Completed" +'</span>';
                    if (value == 3) return '<span style="color:#F42A12;" data-qtitle="Failed" data-qwidth="200" '+
                    'data-qtip="Branch for redemption failed">'+
                    "Failed" +'</span>';
                    if (value == 4) return '<span data-qtitle="Process Delivery" data-qwidth="200" '+
                    'data-qtip="Redemption is being processed for delivery">'+
                    "Process Delivery" +'</span>';
                    if (value == 5) return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                    'data-qtip="Redemption was cancelled by merchant">'+
                    "Cancelled" +'</span>';
                    if (value == 6) return '<span data-qtitle="Reversed" data-qwidth="200" '+
                    'data-qtip="Logistic is being reversed">'+
                    "Reversed" +'</span>';
                    if (value == 7) return '<span style="color:#F42A12;" data-qtitle="Failed Delivery" data-qwidth="200" '+
                    'data-qtip="Logistic/Delivery for redemption failed">'+
                    "Failed Delivery" +'</span>';
                    else return '';
            },
        },
        { text: 'Logistic Fee Payment Mode',  dataIndex: 'logisticfeepaymentmode', minWidth:130, renderer: 'setTextColor',

                filter: {
                    type: 'combo',
                    store: [
                        ['CONTAINER', 'CONTAINER'],
                        ['FPX', 'FPX'],
                        ['GOLD', 'GOLD'],
                        ['WALLET', 'WALLET'],
                        //['Collecting', 10],

                    ],

                },
                renderer: function(value, rec){
                    if (value == 'CONTAINER') return 'CONTAINER';
                    if (value == 'FPX') return 'FPX';
                    if (value == 'GOLD') return 'GOLD';
                    if (value == 'WALLET') return 'WALLET';
                    else return '';
            },
        },
        { text: 'Remarks', dataIndex: 'rdmremarks',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner ID', dataIndex: 'rdmpartnerid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Branch ID', dataIndex: 'rdmbranchid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Product ID', dataIndex: 'rdmproductid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner Ref No', dataIndex: 'rdmpartnerrefno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
            renderer: function(value){
                return '<span style="color:#006600;">' + value + '</span>';
            }
        },
        { text: 'Redemption No', dataIndex: 'rdmredemptionno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
            renderer: function(value){
                return '<span style="color:#006600;">' + value + '</span>';
            } 
        },
        { text: 'API Version', dataIndex: 'apiversion',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },


        { text: 'Premium Fee', dataIndex: 'premiumfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
            }
        },
        { text: 'Handling Fee', dataIndex: 'handlingfee',  exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 2
        }   },
        { text: 'Insurance Fee', dataIndex: 'rdminsurancefee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 2
            }
        },
        { text: 'Packing & Shipment', dataIndex: 'courierfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        { text: 'Marketing Fund', dataIndex: 'commissionfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        { text: 'Total Fee', dataIndex: 'rdmtotalfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        // { text: 'Special Delivery Fee', dataIndex: 'rdmspecialdeliveryfee',  exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        // editor: {    //field has been deprecated as of 4.0.5
        //     xtype: 'numberfield',
        //     decimalPrecision: 2
        // }   },
        /*
        { text: 'Brand', dataIndex: 'xaubrand', renderer: 'setTextColor',hidden:true,filter: { type: 'string' } },
        { text: 'Serial No', dataIndex: 'xauserialno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'XAU', dataIndex: 'xau', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },      */ 
        //{ text: 'Booking On', dataIndex: 'bookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
        { text: 'Booking On', dataIndex: 'rdmbookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Booking Price', dataIndex: 'rdmbookingprice',  exportdecimal:3, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }  },
        { text: 'Booking Price Strem ID', dataIndex: 'rdmbookingpricestreamid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Confirm On', dataIndex: 'confirmon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },  
        { text: 'Confirm On', dataIndex: 'rdmconfirmon',minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Confirm Pricestream ID', dataIndex: 'rdmconfirmpricestreamid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Confirmed Price', dataIndex: 'rdmconfirmprice',  exportdecimal:3, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Confirm Reference', dataIndex: 'rdmconfirmreference',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Address', dataIndex: 'rdmdeliveryaddress',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Delivery Address 1', dataIndex: 'deliveryaddress1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Delivery Address 2', dataIndex: 'deliveryaddress2',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Delivery Address 3', dataIndex: 'deliveryaddress3',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Postcode', dataIndex: 'rdmdeliverypostcode',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery State', dataIndex: 'rdmdeliverystate',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Country', dataIndex: 'rdmdeliverycountry',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Contact Name 1', dataIndex: 'rdmdeliverycontactname1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },     
        { text: 'Delivery Contact Name 2', dataIndex: 'rdmdeliverycontactname2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },    
        { text: 'Delivery Contact No 1', dataIndex: 'rdmdeliverycontactno1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },     
        { text: 'Delivery Contact No 2', dataIndex: 'rdmdeliverycontactno2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },    
        //{ text: 'Processed On', dataIndex: 'processedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
        //{ text: 'Delivered On', dataIndex: 'deliveredon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
        //{ text: 'Created On', dataIndex: 'createdon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' }, },

        //{ text: 'Processed On', dataIndex: 'rdmprocessedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        //{ text: 'Delivered On', dataIndex: 'rdmdeliveredon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Created On', dataIndex: 'createdon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },

        { text: 'Created By', dataIndex: 'createdby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date'} },
        { text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date'} },
        { text: 'Modified By', dataIndex: 'modifiedby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },

    ],
    formClass: 'snap.view.redemption.RedemptionGridForm',
    listeners: {
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {
            var permission = snap.getApplication().hasPermission('/root/dg999/redemption/edit');           
            if (permission === true && record.data.status==1 && record.data.rdmstatus==1) {
                Ext.ComponentQuery.query('#sendToLogistics')[0].enable();
            }else{
                Ext.ComponentQuery.query('#sendToLogistics')[0].disable();
            }
        },
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);            
            columns.find(obj => obj.dataIndex === 'commissionfee').setText(this.partnercode + ' Marketing Fund');
        },
        afterlayout: function(grid){
            var rowExpander = grid.getPlugin("rowexpander")
            var nodes = rowExpander.view.getNodes()
            for (var i = 0; i < nodes.length; i++) {
                rowExpander.expandRow(i);
            } 
        }
    },
});



