
Ext.define('snap.view.redemption.RedemptionRequests', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'redemptionrequests',
    requires: [
        'snap.store.Redemption',
        'snap.model.Redemption',
        'snap.store.SalesPersons',
        'snap.model.SalesPersons',
        'snap.view.redemption.RedemptionController',
        'snap.view.redemption.RedemptionModel',
    ],
    //permissionRoot: '/root/mbb/redemption',
    store: { type: 'Redemption' },
    controller: 'redemption-redemption',
    gridSelectionMode: 'SINGLE',
    allowDeselect:true,
    height: '85%',
    enableFilter: true,
    selType: 'rowmodel',
    
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
        // {reference: 'summaryButton', text: 'Summary', itemId: 'summaryOfRedemption', tooltip: 'Summary', iconCls: 'x-fa fa-list-alt', handler: 'summaryOfRedemption', validSelection: 'single' }
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            handlerModule: 'redemption', text: 'Download', tooltip: 'Print Report',iconCls: 'x-fa fa-print', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            handlerModule: 'redemption', text: 'Export', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }
    ],   
    columns: [
        { text: 'ID', dataIndex: 'id', renderer: 'setTextColor' ,hidden:true,filter: { type: 'int' }},
        { text: 'Type', dataIndex: 'type', renderer: 'setTextColor',filter: { type: 'string' },minWidth:130 },
        // {
        //     text: 'Status', dataIndex: 'status', renderer: function (value, store) {
        //         if (value == 0) return 'Pending';
        //         if (value == 1) return 'Confirmed';
        //         if (value == 2) return 'Completed';
        //         if (value == 3) return 'Failed';
        //         if (value == 4) return 'Process Delivery';
        //         if (value == 5) return 'Cancelled';
        //         else return '';
        //     },
        // },
        { text: 'Status',  dataIndex: 'status', minWidth:130, renderer: 'setTextColor',

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
                        ['8', 'Success'],
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
                    if (value == 8) return '<span style="color:#32CD32;" data-qtitle="Success" data-qwidth="200" '+
                    'data-qtip="Branch for redemption success">'+
                     "Success" +'</span>';
                    else return '';
              },
        },
        { text: 'Partner Ref No', dataIndex: 'partnerrefno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
            renderer: function(value){
                return '<span style="color:#006600;">' + value + '</span>';
            }
        },
        { text: 'Redemption No', dataIndex: 'redemptionno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
            renderer: function(value){
                return '<span style="color:#006600;">' + value + '</span>';
            } 
        },
        { text: 'Remarks', dataIndex: 'remarks',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Partner ID', dataIndex: 'partnerid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Branch Code', dataIndex: 'branchid',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' }, },
        { text: 'Branch Name', dataIndex: 'branchname',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' }, },
        { text: 'Product ID', dataIndex: 'productid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Total Weight', dataIndex: 'totalweight',minWidth:130, exportdecimal:3, renderer: 'setTextColor',filter: { type: 'string' }
            ,align: 'right'
            ,renderer: Ext.util.Format.numberRenderer('0,000.000')
            ,editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Total Items', dataIndex: 'totalquantity',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } 
            ,align: 'right'
        },
        { text: 'API Version', dataIndex: 'apiversion',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },


        { text: 'Redemption Fee', dataIndex: 'redemptionfee',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Insurance Fee', dataIndex: 'insurancefee',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Handling Fee', dataIndex: 'handlingfee',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Special Delivery Fee', dataIndex: 'specialdeliveryfee',hidden:true, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Brand', dataIndex: 'xaubrand', renderer: 'setTextColor',hidden:true,filter: { type: 'string' } },
        // { text: 'Serial No', dataIndex: 'items', renderer: 'setSerialno', minWidth:130, filter: { type: 'string' } },      
        { text: 'Confirm Reference', dataIndex: 'confirmreference',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        
        { text: 'Delivery Address 1', dataIndex: 'deliveryaddress1',minWidth:150, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Address 2', dataIndex: 'deliveryaddress2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Address 3', dataIndex: 'deliveryaddress3',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Post Code', dataIndex: 'deliverypostcode',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery State', dataIndex: 'deliverystate',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Delivery Contact Name 1', dataIndex: 'deliverycontactname1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },        
        { text: 'Delivery Contact No 1', dataIndex: 'deliverycontactno1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },        
        { text: 'Delivery Contact Name 2', dataIndex: 'deliverycontactname2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },        
        { text: 'Delivery Contact No 2', dataIndex: 'deliverycontactno2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },        
        { text: 'Appointment Date', dataIndex: 'appointmentdatetime',minWidth:150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },       
        { text: 'Appointment On', dataIndex: 'appointmenton',minWidth:150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
        { text: 'Created On', dataIndex: 'createdon',minWidth:150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        { text: 'Created By', dataIndex: 'createdby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
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
    formClass: 'snap.view.redemption.RedemptionGridForm',
    listeners: {
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {
            var permission = snap.getApplication().hasPermission('/root/mbb/redemption/edit');           
            if (permission === true && record.data.status==1) {
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


