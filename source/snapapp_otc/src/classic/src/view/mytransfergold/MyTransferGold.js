Ext.define('snap.view.mytransfergold.MyTransferGold', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mytransfergoldview',
    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    detailViewWindowHeight: 500,
    //permissionRoot: '/root/bmmb/report/commission',
    store: { type: 'MyTransferGold' },
    controller: 'mytransfergold-mytransfergold',
    viewModel: {
        type: 'mytransfergold-mytransfergold'
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
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            text: 'Download', tooltip: 'Download Excel', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Print Receipt', cls: '', tooltip: 'Print Transfer Receipt', validSelection: 'single', iconCls: 'x-fa fa-print', reference: 'transferreceiptfromlist', handler: 'transferreceiptfromlist',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Print Transfer Confirmation', cls: '', tooltip: 'Print Transfer Confirmation', validSelection: 'single', iconCls: 'x-fa fa-print', reference: 'printconfirmtransferfromlist', handler: 'printconfirmtransferfromlist',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
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
        { text: 'Partner ID', dataIndex: 'frompartnerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 200, hidden: true},
        { text: 'Transaction Branch', dataIndex: 'transpartnername', filter: { type: 'string' }, minWidth: 200, hidden: true},
	    // { text: 'Bank Name', dataIndex: 'dbmbankname', filter: { type: 'string' }, minWidth: 130, hidden: true },
        {
            text: 'Type', dataIndex: 'type',
            filter: {
                type: 'combo',
                store: [
                    ['INVITE', 'INVITE'],
                    ['TRANSFER', 'TRANSFER'],
                    ['RENT', 'RENT'],
                ],
                renderer: function (value, rec) {
                    if (value == 'INVITE') return 'INVITE';
                    else if (value == 'TRANSFER') return 'TRANSFER';
                    else if (value == 'RENT') return 'RENT';
                    else return 'UNIDENTIFIED';
                },
            },

        },
        { text: 'Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, hidden: true },
        { text: 'From', dataIndex: 'fromfullname', filter: { type: 'string' }, minWidth: 130 },
        { text: 'To', dataIndex: 'tofullname', filter: { type: 'string' }, minWidth: 130 },
        { text: 'From Account Code', dataIndex: 'fromaccountholdercode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'To Account Code', dataIndex: 'toaccountholdercode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        // { text: 'Contact', dataIndex: 'contact', filter: { type: 'string' }, minWidth: 130 },
      
        {
            text: 'Price', dataIndex: 'price', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Xau Weight (g)', dataIndex: 'xau', exportdecimal: 3, filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        },
        {
            text: 'Total Amount (RM)', dataIndex: 'amount', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
      
        { text: 'Teller Remarks', dataIndex: 'message', filter: { type: 'string' }, minWidth: 130, hidden: true },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],                    
                    ['1', 'Success'],
                    ['2', 'Failed'],
                    ['3', 'Require Approval'],
                    ['4', 'Timeout Approval'],
                    ['5', 'Reject Approval'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending';
                else if (value == '1') return 'Success';
                else if (value == '2') return 'Failed';
                else if (value == '3') return 'Require Approval';
                else if (value == '4') return 'Timeout Approval';
                else if (value == '5') return 'Reject Approval';
            },
        },
        {
            text: 'Notified Receipient', dataIndex: 'isnotifyrecipient', minWidth: 80,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span style="color:#800080;">' + 'No' + '</span>';
                else if (value == '1') return '<span style="color:#d4af37;">' + 'Yes' + '</span>';
                else return 'No';
            },
        },
        // { text: 'Transfer On', dataIndex: 'transferon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        // { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 100, },
        // { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Transfer Made On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Checker', dataIndex: 'checker', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Action On', dataIndex: 'actionon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },

    ],
});
