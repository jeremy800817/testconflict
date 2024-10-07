Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeBumira', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mybumiradailystoragefeeview',

    partnercode: 'BUMIRA',
    permissionRoot: '/root/bumira/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=BUMIRA',
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
