Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFee', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mymonthlystoragefeeview',

    requires: [
        'snap.store.MyMonthlyStorageFee',
        'snap.model.MyMonthlyStorageFee',
        'snap.view.mymonthlystoragefee.MyMonthlyStorageFeeController',
        'snap.view.mymonthlystoragefee.MyMonthlyStorageFeeModel'
    ],
    permissionRoot: '/root/bmmb/report/storagefee',
    store: { type: 'MyMonthlyStorageFee' },
    controller: 'mymonthlystoragefee-mymonthlystoragefee',

    viewModel: {
        type: 'mymonthlystoragefee-mymonthlystoragefee'
    },
    partnercode: '',

    detailViewWindowHeight: 500,
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
        { text: 'Date & Time', dataIndex: 'chargedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Ace Ref', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Cust Code', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Cust Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Customer XAU Holding (g)', exportdecimal: 3, dataIndex: 'ledcurrentxau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Gold Price (Rm/g)', exportdecimal: 2, dataIndex: 'price', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
        { text: 'Admin Fee XAU Charge (g)', exportdecimal: 3, dataIndex: 'adminfeexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Admin Fee Amount (RM)', exportdecimal: 2, dataIndex: 'adminfeeamount', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
        { text: 'Storage Fee XAU Charge (g)', exportdecimal: 3, dataIndex: 'storagefeexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Storage Fee Amount (RM)', exportdecimal: 2, dataIndex: 'storagefeeamount', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
        // { text: 'XAU Charge (g)', exportdecimal: 3, dataIndex: 'xau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        // { text: 'Amount', exportdecimal: 2, dataIndex: 'amount', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00')},
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Completed'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending';
                else if (value == '1') return 'Completed';
            },
        },
    ],
});
