Ext.define('snap.view.logistics.MibLogistic', {
    extend: 'snap.view.logistics.Logistic',
    xtype: 'miblogisticview',

    requires: [

        'snap.store.MibLogistic',
        'snap.model.MibLogistic',
        'snap.view.logistics.LogisticController',
        'snap.view.logistics.LogisticModel',
        'Ext.ProgressBarWidget',

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/mbb/logistic',
    store: {
        type: 'MibLogistic'
    },
    controller: 'logistic-logistic',
    viewModel: {
        type: 'logistic-logistic',
    },
    enableFilter: true,
    gridSelectionMode: 'SINGLE',
    toolbarItems: [
        //'add', 'edit''detail', '|', 'delete', 'filter','|',
        {
            reference: 'editLgsStatusButton',
            text: 'Edit Logistic Buyback Status',
            itemId: 'editStatusLgs',
            tooltip: 'Update Logistic Status For Buyback',
            iconCls: 'x-fa fa-edit',
            handler: 'editDeliveryStatus',
            validSelection: 'single',
            listeners: {
                afterrender: function (srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target: srcCmp.getEl(),
                        html: 'Edit Logistic Status'
                    });
                }
            }
        },
        'detail', '|', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },

        '|',
        {
            reference: 'assignAceSalesmanButton',
            text: 'Assign Ace Salesman',
            itemId: 'assignAceSalesman',
            tooltip: 'Assign Salesman for delivery',
            iconCls: 'x-fa fa-paper-plane',
            handler: 'assignAceSalesman',
            validSelection: 'single',
            listeners: {
                afterrender: function (srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target: srcCmp.getEl(),
                        html: 'Assign Salesman for delivery'
                    });
                }
            }
        },
        {
            reference: 'updateLgsStatusButton',
            text: 'Logistic Status',
            itemId: 'statusLgs',
            tooltip: 'Update Logistic Status',
            iconCls: 'x-fa fa-list-alt',
            handler: 'updateDeliveryStatus',
            validSelection: 'single',
            listeners: {
                afterrender: function (srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target: srcCmp.getEl(),
                        html: 'Update Logistic Status'
                    });
                }
            }
        },
        {
            reference: 'minimizeLgsButton',
            text: 'Minimize Listing',
            itemId: 'minimizeLgs',
            tooltip: 'Minimize Logistic Listing',
            iconCls: 'x-fa fa-arrow-left',
            handler: 'minimizeGridColumnMIB',
            validSelection: 'ignore'
        },
        {
            reference: 'expandLgsButton',
            text: 'Expand Listing',
            itemId: 'expandLgs',
            tooltip: 'Expand Logistic Listing',
            iconCls: 'x-fa fa-arrow-right',
            handler: 'expandGridColumnMIB',
            validSelection: 'ignore'
        },
        '|',
        {
            reference: 'printButton',
            text: 'Print Document',
            itemId: 'printButton',
            tooltip: 'Print Documents',
            iconCls: 'x-fa fa-print',
            handler: 'printButton',
            validSelection: 'single'
        },
        //'|',
        //{reference: 'createGdexPickupDatetime', text: 'Create GDex Pickup Time', itemId: 'createGdexPickupDatetime', tooltip: 'Create GDex Pickup Time', iconCls: 'x-fa fa-arrow-right', handler: 'createGdexPickupDatetime', validSelection: 'ignore' },
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
    id: 'miblogisticgrid',
    checkActionPermission: function (view, record) {
        var selected = false;
        Ext.Array.each(
            view.getSelectionModel().getSelection(),
            function (items) {
                if (items.getId() == record.getId()) {
                    selected = true;
                    return false;
                }
            }
        );

        // For Buyback
        var btneditStatusLgs = Ext.ComponentQuery.query("#editStatusLgs")[0];
        // For Redemption / Replenishment
        var btnstatusLgs = Ext.ComponentQuery.query("#statusLgs")[0];


        var btnassignAceSalesman = Ext.ComponentQuery.query(
            "#assignAceSalesman"
        )[0];
        btneditStatusLgs.disable();
        btnstatusLgs.disable();
        btnassignAceSalesman.disable();

        var vendorvalue = view.getSelectionModel().getSelection()[0].data
            .vendorvalue;
        var usertype = view.getSelectionModel().getSelection()[0].data.usertype;
        var ordertype = view.getSelectionModel().getSelection()[0].data.type;

        if (vendorvalue == "CourAce" && usertype == "Operator" && selected) {
            btnassignAceSalesman.enable();
            btnstatusLgs.enable();
        }
        // For buyback
        if ("Buyback" == ordertype && selected) {
            btneditStatusLgs.enable();
            btnstatusLgs.enable();
        }else{

        }
        // For redemption/ replenishment
        if (("Redemption" == ordertype || "Replenishment" == ordertype ) && selected) {
            btnstatusLgs.enable();
        }else{

        }
    },
});