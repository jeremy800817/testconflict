Ext.define('snap.view.mydailystoragefee.MyDailyStorageFee', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mydailystoragefeeview',

    requires: [
        'snap.store.MyDailyStorageFee',
        'snap.model.MyDailyStorageFee',
        'snap.view.mydailystoragefee.MyDailyStorageFeeController',
        'snap.view.mydailystoragefee.MyDailyStorageFeeModel'
    ],
    permissionRoot: '/root/bmmb/report/storagefee',
    store: { type: 'MyDailyStorageFee' },
    controller: 'mydailystoragefee-mydailystoragefee',

    viewModel: {
        type: 'mydailystoragefee-mydailystoragefee'
    },

    detailViewWindowHeight: 400,
    accountHolderId: 0,

    enableFilter: true,
    sortableColumns: false,
    toolbarItems: [
        'detail', '|' ,'filter', '|' ,
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Print', tooltip: 'Print Report', iconCls: 'x-fa fa-print', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
        }
    },
    columns: [
        { text: 'Date & Time', dataIndex: 'calculatedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Cust Code', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Cust Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Customer XAU Holding (g)', exportdecimal: 3, dataIndex: 'balancexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Admin Fee Calculated (g)', exportdecimal: 6, dataIndex: 'adminfeexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000000') },
        { text: 'Storage Fee Calculated (g)', exportdecimal: 6, dataIndex: 'storagefeexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000000') },
        { text: 'Xau Calculated (g)', exportdecimal: 6, dataIndex: 'xau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000000') },
    ],
});
