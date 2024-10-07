Ext.define('snap.view.myledger.MyLedgerKopttr', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerkopttrview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/kopttr/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=KOPTTR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'KOPTTR',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Download', tooltip: 'Download Excel', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReportKtp', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
    ],
});
