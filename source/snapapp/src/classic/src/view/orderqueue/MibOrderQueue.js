Ext.define('snap.view.orderqueue.MibOrderQueue',{
    extend: 'snap.view.orderqueue.OrderQueue',
    xtype: 'miborderqueueview',

    requires: [
        'snap.store.MibOrderQueue',
        'snap.model.MibOrderQueue',
        'snap.view.orderqueue.OrderQueueController',
        'snap.view.orderqueue.OrderQueueModel'
    ],
    permissionRoot: '/root/mbb/ftrorder',
    store: { type: 'MibOrderQueue' },
    controller: 'orderqueue-orderqueue',

    viewModel: {
        type: 'orderqueue-orderqueue'
    },

    detailViewWindowHeight: 400,

    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter','|', 
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', style : "width : 130px;",  text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-times-circle', style : "width : 130px;",  text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
        {
            handlerModule: 'miborderqueue', style : "width : 130px;", text: 'Export', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
        },
    ],

});
