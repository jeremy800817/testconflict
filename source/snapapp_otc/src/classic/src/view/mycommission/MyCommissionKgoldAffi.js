Ext.define('snap.view.mycommission.MyCommissionKgoldAffi', {
    extend: 'snap.view.mycommission.MyCommissionKtp',
    xtype: 'mykgoldafficommissionview',
    partnercode: 'KGOLDAFFI',
    permissionRoot: '/root/kgoldaffi/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=KGOLDAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
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
            text: 'Print', tooltip: 'Print', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReportKtp', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            text: 'Show List',tooltip: 'Show Partner List',iconCls: 'x-fa fa-list', reference: 'getpartnerlisting', handler: 'getPartnerListing',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
    ],
});