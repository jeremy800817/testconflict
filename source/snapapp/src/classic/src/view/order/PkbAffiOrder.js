Ext.define('snap.view.order.PkbAffiOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'pkbaffiorderview',
    requires: [

        'snap.store.Order',
        'snap.model.Order',
        'snap.view.order.OrderController',
        'snap.view.order.OrderModel',


    ],
    partnercode: 'PKBAFFI',
    permissionRoot: '/root/pkbaffi/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=PKBAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    controller: 'ktporder-ktporder',
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            text: 'Download',tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'openDownloadGridForm',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Export Zip', tooltip: 'Export Zip To Email', iconCls: 'x-fa fa-envelope', handler: 'getPrintReportJob',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-edit', text: 'Single Update Ref. No.', itemId: 'editRefNoBmmb', tooltip: 'Edit Reference No. for Company Buy', handler: 'editReferenceNo', showToolbarItemText: true,
        },
        {
            reference: 'uploadbulkpaymentresponse',iconCls: 'x-fa fa-edit', text: 'Batch Update Ref. No.', itemId: 'updateRefNo', tooltip: 'Upload Maybank Response', handler: 'uploadbulkpaymentresponse', showToolbarItemText: true,
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Upload Maybank Response'
                    });
                }
            }
        },
        {
            iconCls: 'x-fa fa-exclamation-circle', text: 'Update Refund Status', itemId: 'editPendingRefundStatus', tooltip: 'Update Pending Refund Status For Order', handler: 'editPendingRefundStatus', showToolbarItemText: true,
        },
    ], 
});
