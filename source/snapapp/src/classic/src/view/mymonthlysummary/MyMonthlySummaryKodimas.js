Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryKodimas', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mykodimasmonthlysummaryview',
    partnercode: 'KODIMAS',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=KODIMAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'monthfield', fieldLabel: 'Month', reference: 'monthEnd', itemId: 'monthEnd', format: 'm/Y', menu: { items: [] }, name: 'monthEndOn', labelWidth: 'auto'
        },
        { reference: 'printSummaryBtn', handler: 'getPrintReportKtp', text: 'Print Summary', itemId: 'printSummaryBtn', tooltip: 'Print Summary', iconCls: 'x-fa fa-print', validSelection: 'ignore', showToolbarItemText: true },
        { reference: 'printTransactionBtn', handler: 'getTransactionReport', text: 'Print Transaction', itemId: 'printTransactionBtn', tooltip: 'Print Transaction', iconCls: 'x-fa fa-print', validSelection: 'single',  showToolbarItemText: true },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
    ],
});