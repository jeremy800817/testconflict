Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeBumira', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mybumiramonthlystoragefeeview',

    partnercode: 'BUMIRA',
    permissionRoot: '/root/bumira/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=BUMIRA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    toolbarItems: [
        'detail', '|' ,'filter', '|' ,
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Print', tooltip: 'Print Report', iconCls: 'x-fa fa-print', handler: 'getPrintReportBumira', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
    ],
});