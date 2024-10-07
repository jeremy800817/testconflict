Ext.define('snap.view.myadminfee.MyAdminFee', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myadminfeeview',
    requires: [
        'snap.store.MyAdminFee',
        'snap.model.MyAdminFee',
        'snap.view.myadminfee.MyAdminFeeController',
        'snap.view.myadminfee.MyAdminFeeModel'
    ],
    store: { type: 'MyAdminFee' },
    controller: 'myadminfee-myadminfee',

    viewModel: {
        type: 'myadminfee-myadminfee'
    },
    partnercode: '',

    detailViewWindowHeight: 400,
    accountHolderId: 0,

    enableFilter: true,
    sortableColumns: false,
    toolbarItems: [
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
        { text: 'Cust Code', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Cust Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Gold Price (Rm/g)', exportdecimal: 2, dataIndex: 'price', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'XAU Charge (g)', exportdecimal: 3, dataIndex: 'adminfeexau', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount (RM)', exportdecimal: 2, dataIndex: 'adminfeeamount', filter: { type: 'string' }, minWidth: 100, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
    ],
});
