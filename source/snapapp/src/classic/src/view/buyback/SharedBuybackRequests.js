
Ext.define('snap.view.buyback.SharedBuybackRequests', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'sharedbuybackrequests',
    requires: [
        'snap.store.SharedBuyback',
        'snap.model.SharedBuyback',
        'snap.store.SalesPersons',
        'snap.model.SalesPersons',
        'snap.view.buyback.BuybackController',
        'snap.view.buyback.BuybackModel',
    ],
    store: { type: 'SharedBuyback' },
    controller: 'buyback-buyback',
    gridSelectionMode: 'SINGLE',
    allowDeselect:true,
    height: '85%',
    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter','|',
        // { reference: 'sendButton', text: 'Send', itemId: 'sendToLogistics', tooltip: 'Send to Logistics', iconCls: 'x-fa fa-paper-plane', handler: 'addLogistic', validSelection: 'ignore' },

        // '|',
        // {reference: 'summaryButton', text: 'Summary', itemId: 'summaryOfRedemption', tooltip: 'Summary', iconCls: 'x-fa fa-list-alt', handler: 'summaryOfRedemption', validSelection: 'single' }
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            handlerModule: 'sharedbuyback', text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }
    ],   
    columns: [
        { text: 'ID', dataIndex: 'id', renderer: 'setTextColor' ,hidden:true,filter: { type: 'int' }},
        //{ text: 'Type', dataIndex: 'type', renderer: 'setTextColor',filter: { type: 'string' },minWidth:130 },
        /*{
            text: 'Status', dataIndex: 'status', renderer: function (value, store) {
                if (value == 0) return 'Pending';
                if (value == 1) return 'Confirmed';
                if (value == 2) return 'Completed';
                if (value == 3) return 'Failed';
                if (value == 4) return 'Process Delivery';
                if (value == 5) return 'Cancelled';
                else return '';

                
            },
        },*/
        { text: 'Status',  dataIndex: 'status', minWidth:130, renderer: 'setTextColor',

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Pending'],
                       ['1', 'Confirmed'],
                       ['2', 'Process Collect'],
                       ['3', 'Completed'],
                       ['4', 'Failed'],
                       ['5', 'Reversed'],
                       //['Collecting', 10],

                   ],

               },
               renderer: function(value, rec){
                    if (value == 0) return 'Pending';
                    if (value == 1) return 'Confirmed';
                    if (value == 2) return 'Process Collect';
                    if (value == 3) return 'Completed';
                    if (value == 4) return 'Failed';
                    if (value == 5) return 'Reversed';
                    else return '';
              },
        },
        { text: 'Created On', dataIndex: 'createdon',minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        { text: 'Confirm On', dataIndex: 'confirmon', minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Partner Ref No', dataIndex: 'partnerrefno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' }, renderer: 'boldText' },
        { text: 'Buyback No', dataIndex: 'buybackno', minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Total Weight', dataIndex: 'totalweight',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'GP', dataIndex: 'price',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Discount', dataIndex: 'fee', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { 
            text: 'FP', 
            hidden:false, 
            renderer: 'setTextColor',
            filter: { type: 'string' }, 
            align: 'right', 
            minWidth: 130, 
            renderer: function(val, meta, record, rowIndex){
                var newValue = record.get('price') + record.get('fee');
                return Ext.util.Format.number(newValue, '0,000.000');
            },
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }   
        },
        { text: 'Confirm Price', dataIndex: 'confirmprice', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },       
        { text: 'Total Amount', dataIndex: 'totalamount',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Remarks', dataIndex: 'remarks',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner ID', dataIndex: 'partnerid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Branch ID', dataIndex: 'branchid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Product ID', dataIndex: 'productid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
      
        { text: 'API Version', dataIndex: 'apiversion',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Price Stream ID', dataIndex: 'pricestreamid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },

       

     

        { text: 'Total Quantity', dataIndex: 'totalquantity',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
      
        { text: 'Items', dataIndex: 'items', hidden:true, renderer: 'setTextColor', filter: { type: 'string' } },

        { text: 'Partner Name', dataIndex: 'partnername', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Partner Code', dataIndex: 'partnercode', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Branch Name', dataIndex: 'branchname', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Branch Code', dataIndex: 'branchcode', renderer: 'setTextColor', filter: { type: 'string' } },

        { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        
        //{ text: 'Booking On', dataIndex: 'bookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },

        { text: 'Collected On', dataIndex: 'collectedon', minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Collected By', dataIndex: 'collectedby', hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Reconciled',  dataIndex: 'reconciled', hidden:true, flex: 1,

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
        { text: 'Reconciled On', dataIndex: 'reconciledon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Rconciled By', dataIndex: 'reconciledby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },  
        { text: 'Reconciled SAP Ref No', dataIndex: 'reconciledsaprefno',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Confirm On', dataIndex: 'confirmon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },  
       

        { text: 'Created By', dataIndex: 'createdby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date'} },
        { text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date'} },
        { text: 'Modified By', dataIndex: 'modifiedby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },

    ],
    formClass: 'snap.view.buyback.BuybackGridForm',
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
});



