Ext.define('snap.view.mymonthlysummary.MyMonthlySummary', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mymonthlysummaryview',
    requires: [
        'snap.store.MyMonthlySummary',
        'snap.model.MyMonthlySummary',
        'snap.view.mymonthlysummary.MyMonthlySummaryController',
        'snap.view.mymonthlysummary.MyMonthlySummaryModel',
    ],
    detailViewWindowHeight: 500,
    permissionRoot: '/root/bmmb/report/monthlysummary',
    store: { type: 'MyMonthlySummary' },
    controller: 'mymonthlysummary-mymonthlysummary',
    viewModel: {
        type: 'mymonthlysummary-mymonthlysummary'
    },
    partnercode: '',
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'monthfield', fieldLabel: 'Month', reference: 'monthEnd', itemId: 'monthEnd', format: 'm/Y', menu: { items: [] }, name: 'monthEndOn', labelWidth: 'auto'
        },
        { reference: 'printSummaryBtn', handler: 'getPrintReport', text: 'Print Summary', itemId: 'printSummaryBtn', tooltip: 'Print Summary', iconCls: 'x-fa fa-print', validSelection: 'ignore', showToolbarItemText: true },
        { reference: 'printTransactionBtn', handler: 'getTransactionReport', text: 'Print Transaction', itemId: 'printTransactionBtn', tooltip: 'Print Transaction', iconCls: 'x-fa fa-print', validSelection: 'single',  showToolbarItemText: true },
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
            var columns = this.query('gridcolumn');
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },

    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
        { text: 'Accountcode', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'My Kad No', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Xau Balance', dataIndex: 'xaubalance', exportdecimal:3, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: (val) => Ext.util.Format.number(Ext.Number.roundToPrecision(val, 3), '0,000.000') },
        { text: 'Amount Balance', dataIndex: 'amountbalance', exportdecimal: 2, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: (val) => Ext.util.Format.number(Ext.Number.roundToPrecision(val, 2), '0,000.00') },
        { text: 'Phone', dataIndex: 'phoneno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Address Line 1', dataIndex: 'addressline1', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Address Line 2', dataIndex: 'addressline2', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Postcode', dataIndex: 'addresspostcode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'City', dataIndex: 'addresscity', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'State', dataIndex: 'addressstate', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    ],
});
