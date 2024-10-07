Ext.define('snap.view.logistics.PosLogistic',{
    extend: 'snap.view.logistics.Logistic',
    xtype: 'poslogisticview',

    requires: [

        'snap.store.PosLogistic',
        'snap.model.PosLogistic',
        'snap.view.logistics.LogisticController',
        'snap.view.logistics.LogisticModel',
        'Ext.ProgressBarWidget',

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/mbb/logistic',
    store: { type: 'PosLogistic' },
    controller: 'logistic-logistic',
    viewModel: {
        type: 'logistic-logistic',
    },
    enableFilter: true,
    gridSelectionMode: 'SINGLE',
    toolbarItems: [
      //'add', 'edit''detail', '|', 'delete', 'filter','|',
      'detail', '|', 'filter',
      //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
      //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
     
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
      {reference: 'minimizeLgsButton', text: 'Minimize Listing', itemId: 'minimizeLgs', tooltip: 'Minimize Logistic Listing', iconCls: 'x-fa fa-arrow-left', handler: 'minimizeGridColumnMIB', validSelection: 'ignore' },
      {reference: 'expandLgsButton', text: 'Expand Listing', itemId: 'expandLgs', tooltip: 'Expand Logistic Listing', iconCls: 'x-fa fa-arrow-right', handler: 'expandGridColumnMIB', validSelection: 'ignore' },
      '|',
      {reference: 'printButton', text: 'Print Document', itemId: 'printButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-print', handler: 'printButton', validSelection: 'single' },
      //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'ona', validSelection: 'single' },
  ],
    id: 'miblogisticgrid',

});
