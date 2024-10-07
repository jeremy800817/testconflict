Ext.define('snap.view.logistics.GtpLogistic',{
    extend: 'snap.view.logistics.Logistic',
    xtype: 'gtplogisticview',

    requires: [

        'snap.store.GtpLogistic',
        'snap.model.GtpLogistic',
        'snap.view.logistics.LogisticController',
        'snap.view.logistics.LogisticModel',
        'Ext.ProgressBarWidget',

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/gtp/logistic',
    store: { type: 'GtpLogistic' },
    controller: 'logistic-logistic',
    viewModel: {
        type: 'logistic-logistic',
    },
    enableFilter: true,
    gridSelectionMode: 'SINGLE',
  
    toolbarItems: [
        {reference: 'editLgsStatusButton', text: 'Edit Logistic Status', itemId: 'editStatusLgs', tooltip: 'Update Logistic Status', iconCls: 'x-fa fa-edit', handler: 'editDeliveryStatus', validSelection: 'single',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Edit Logistic Status'
                    });
                }
            }
        },
        //'add', 'edit''detail', '|', 'delete', 'filter','|',
        'detail', '|', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        '|',
        { reference: 'assignAceSalesmanButton', text: 'Assign Ace Salesman', itemId: 'assignAceSalesman', tooltip: 'Assign Salesman for delivery', iconCls: 'x-fa fa-paper-plane', handler: 'assignAceSalesman', validSelection: 'single',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Assign Salesman for delivery'
                    });
                }
            }
        },
        {reference: 'updateLgsStatusButton', text: 'Logistic Status', itemId: 'statusLgs', tooltip: 'Update Logistic Status', iconCls: 'x-fa fa-list-alt', handler: 'updateDeliveryStatus', validSelection: 'single',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Update Logistic Status'
                    });
                }
            }
        },
        {reference: 'minimizeLgsButton', text: 'Minimize Listing', itemId: 'minimizeLgs', tooltip: 'Minimize Logistic Listing', iconCls: 'x-fa fa-arrow-left', handler: 'minimizeGridColumnGTP', validSelection: 'ignore' },
        {reference: 'expandLgsButton', text: 'Expand Listing', itemId: 'expandLgs', tooltip: 'Expand Logistic Listing', iconCls: 'x-fa fa-arrow-right', handler: 'expandGridColumnGTP', validSelection: 'ignore' },
        // {reference: 'getshipmentstatus', text: 'Get Shipment Status', itemId: 'getshipmentstatus', tooltip: 'Get Shipment Status', iconCls: 'x-fa fa-arrow-right', handler: 'getShipmentStatus', validSelection: 'single' },
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'ona', validSelection: 'single' },

        {
            reference: 'getshipmentdetails',
            text: 'Get Shipment Details',
            itemId: 'getshipmentdetails',
            tooltip: 'Get Shipment Details',
            iconCls: 'x-fa fa-truck',
            handler: 'getShipmentDetails',
            validSelection: 'single',
            showToolbarItemText: true, 
        },

        {
            reference: 'getlatestcourierstatus',
            text: 'Upate Courier Status',
            itemId: 'getlatestcourierstatus',
            tooltip: 'Upate Courier Status',
            iconCls: 'x-fa fa-truck',
            handler: 'getlatestcourierstatus',
            validSelection: 'ignore',
            showToolbarItemText: true, 
        },
    ],

    id: 'gtplogisticgrid',

});
