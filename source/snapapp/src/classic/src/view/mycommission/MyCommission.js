Ext.define('snap.view.mycommission.MyCommission', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mycommissionview',
    requires: [
        'snap.store.MyCommission',
        'snap.model.MyCommission',
        'snap.view.mycommission.MyCommissionController',
        'snap.view.mycommission.MyCommissionModel',
    ],
    detailViewWindowHeight: 500,
    //permissionRoot: '/root/bmmb/report/commission',
    store: { type: 'MyCommission' },
    controller: 'mycommission-mycommission',
    viewModel: {
        type: 'mycommission-mycommission'
    },
    partnercode: '',
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Print', tooltip: 'Print', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
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
            var columns = this.query('gridcolumn');
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },

    viewConfig: {
        getRowClass: function (record) {
            record.data.price = parseFloat(record.data.price).toFixed(3);
            record.data.byweight = record.data.byweight == '1' ? 'Yes' : '';
            record.data.amount = parseFloat(record.data.amount).toFixed(3);
        },
    },

    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'Partner', dataIndex: 'ordpartnername', filter: { type: 'string' }, minWidth: 200, hidden: true},
        // { text: 'Bank Name', dataIndex: 'dbmbankname', filter: { type: 'string' }, minWidth: 130, hidden: true },
        { text: 'Transaction Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, hidden: true },
        { text: 'Customer Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Customer NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Customer Code', dataIndex: 'achcode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Customer Email', dataIndex: 'achemail', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Customer Phone', dataIndex: 'achphoneno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Booking On', dataIndex: 'ordbookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'Order Buyer Id', dataIndex: 'ordbuyerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'Order Cancel On', dataIndex: 'ordcancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        { text: 'Order Confirm On', dataIndex: 'ordconfirmon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        { text: 'Order No', dataIndex: 'ordorderno', filter: { type: 'string' }, minWidth: 130 },
        {
            text: 'Order Price', dataIndex: 'ordprice', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Xau Weight (g)', dataIndex: 'ordxau', exportdecimal: 3, filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        },
        {
            text: 'Total Amount (RM)', dataIndex: 'ordamount', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Incoming Payment (RM)', dataIndex: 'dbmpdtverifiedamount', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'), hidden: true 
        },
        {
            text: 'Ace Buy/Sell', dataIndex: 'ordtype',
            filter: {
                type: 'combo',
                store: [
                    ['CompanySell', 'CompanySell'],
                    ['CompanyBuy', 'CompanyBuy'],
                    ['CompanyBuyBack', 'CompanyBuyBack'],
                ],
                renderer: function (value, rec) {
                    if (value == 'CompanySell') return 'CompanySell';
                    else if (value == 'CompanyBuy') return 'CompanyBuy';
                    else return 'CompanyBuyBack';
                },
            },

        },
        { text: 'Order Partner ID', dataIndex: 'ordpartnerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'Order Remarks', dataIndex: 'ordremarks', filter: { type: 'string' }, minWidth: 130, hidden: true },
        {
            text: 'Status', dataIndex: 'ordstatus', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending Payment'],                    
                    ['1', 'Confirmed'],
                    ['5', 'Completed'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending Payment';
                else if (value == '1') return 'Confirmed';
                else if (value == '5') return 'Completed';
            },
        },
        {
            text: 'Payment Amount (RM)', hidden: true, dataIndex: 'pdtamount', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130 },
        {
            text: 'Transaction Fee (RM)', hidden: true, dataIndex: 'ordfee', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Partner Per Gram (RM)', dataIndex: 'partnercommissionpergram', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Partner Commission (RM)', dataIndex: 'partnercommission', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'ACE Per Gram (RM)', dataIndex: 'acecommissionpergram', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),            
        },
        {
            text: 'ACE Commission (RM)', dataIndex: 'acecommission', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),            
        },
        { text: 'Salesperson Code', dataIndex: 'salespersoncode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Referral Branch Code', dataIndex: 'referralbranchcode', filter: { type: 'string' }, minWidth: 130, hidden: true  },

        { text: 'Completed On', dataIndex: 'completedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        { text: 'Failed On', dataIndex: 'failedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 100, },
        { text: 'Reversed On', dataIndex: 'reversedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },

    ],
});
