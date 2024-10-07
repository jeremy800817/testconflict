
Ext.define('snap.view.buyback.BuybackRequests', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'buybackrequests',
    requires: [
        'snap.store.Buyback',
        'snap.model.Buyback',
        'snap.store.SalesPersons',
        'snap.model.SalesPersons',
        'snap.view.buyback.BuybackController',
        'snap.view.buyback.BuybackModel',
    ],
    permissionRoot: '/root/mbb/buyback',
    store: { type: 'Buyback' },
    controller: 'buyback-buyback',
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
        '<table style="width: 400px;border: 1px solid #d2d2d2;margin-bottom:15px;border-radius:5px;box-shadow: 0px 1px rgba(0,0,0,0.1);margin-left: 20px">',
            '<tr>',
                '<th style="text-align:center; width:200px">Serial No.</th>',
                '<th style="text-align:center; width:200px">Denomination</th>',
            '</tr>',
            '{child}',
        '</table>'
        )                                      
    },
    
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter','|',
        { reference: 'sendButton', text: 'Send', itemId: 'sendToLogistics', tooltip: 'Send to Logistics', iconCls: 'x-fa fa-paper-plane', handler: 'addLogistic', validSelection: 'ignore' },

        '|',
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
            handlerModule: 'buyback', text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
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
                    if (value == 0) return '<span data-qtitle="Pending" data-qwidth="200" '+
                    'data-qtip="Buyback is pending for the next action">'+
                     "Pending" +'</span>';
                    if (value == 1) return '<span data-qtitle="Confirmed" data-qwidth="200" '+
                    'data-qtip="Buyback request is confirmed for collection">'+
                     "Confirmed" +'</span>';
                    if (value == 2) return '<span data-qtitle="Process Collect" data-qwidth="200" '+
                    'data-qtip="Buyback is being processed for collection">'+
                     "Process Collect" +'</span>';
                    if (value == 3) return '<span data-qtitle="Completed" data-qwidth="200" '+
                    'data-qtip="Buyback is successful or collected">'+
                     "Completed" +'</span>';
                    if (value == 4) return '<span data-qtitle="Failed" data-qwidth="200" '+
                    'data-qtip="Logistic/Collection for buyback failed">'+
                     "Failed" +'</span>';
                    if (value == 5) return '<span data-qtitle="Reversed" data-qwidth="200" '+
                    'data-qtip="Buyback was cancelled by merchant">'+
                     "Reversed" +'</span>';
                    else return '';
              },
        },
        { text: 'Remarks', dataIndex: 'remarks',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner ID', dataIndex: 'partnerid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Branch ID', dataIndex: 'branchid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Product ID', dataIndex: 'productid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner Ref No', dataIndex: 'partnerrefno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Buyback No', dataIndex: 'buybackno', minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'API Version', dataIndex: 'apiversion',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Price Stream ID', dataIndex: 'pricestreamid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },

        { text: 'Price', dataIndex: 'price',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },

        { text: 'Total Weight', dataIndex: 'totalweight',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Total Amount', dataIndex: 'totalamount',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Total Quantity', dataIndex: 'totalquantity',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Fee', dataIndex: 'fee', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        //{ text: 'Items', dataIndex: 'items', hidden:true, renderer: 'setTextColor', filter: { type: 'string' } },

        { text: 'Partner Name', dataIndex: 'partnername', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Partner Code', dataIndex: 'partnercode', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Branch Name', dataIndex: 'branchname', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Branch Code', dataIndex: 'branchcode', renderer: 'setTextColor', filter: { type: 'string' } },

        { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Confirm Price', dataIndex: 'confirmprice', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },       
        //{ text: 'Booking On', dataIndex: 'bookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
        { text: 'Confirm On', dataIndex: 'confirmon', minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
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
        { text: 'Created On', dataIndex: 'createdon',minWidth:130,renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },

        { text: 'Created By', dataIndex: 'createdby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        //{ text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date'} },
        { text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date'} },
        { text: 'Modified By', dataIndex: 'modifiedby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },

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
        },
        afterlayout: function(grid){
            console.log(this,grid,'this',grid.getPlugin("rowexpander").view.getNodes());
            var rowExpander = grid.getPlugin("rowexpander")
            var nodes = rowExpander.view.getNodes()
            for (var i = 0; i < nodes.length; i++) {
                rowExpander.expandRow(i);
            } 
        }
    },
});



