Ext.define('snap.view.myaccountclosure.MyAccountClosureKoponas', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mykoponasaccountclosureview',
    partnercode: 'KOPONAS',
    permissionRoot: '/root/koponas/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=KOPONAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    requires:[
        'snap.view.myaccountclosure.MyAccountClosureController',
    ],
    controller: 'myaccountclosure-myaccountclosure',
    toolbarItems: [
        'detail', '|', 'filter', '|', 
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Print', tooltip: 'Print Report', iconCls: 'x-fa fa-print', handler: 'getPrintReportKtp', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        }
    ],
});
