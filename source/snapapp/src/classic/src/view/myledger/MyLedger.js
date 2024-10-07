Ext.define('snap.view.myledger.MyLedger', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myledgerview',
    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    detailViewWindowHeight: 500,
    //permissionRoot: '/root/bmmb/report/commission',
    store: { type: 'MyLedger' },
    controller: 'myledger-myledger',
    viewModel: {
        type: 'myledger-myledger'
    },
    partnercode: '',
    enableFilter: true,
    enableDetailView: true,
    detailViewWindowHeight: 650,
    //detailViewWindowWidth: 500,
    style: "word-wrap: normal",
    detailViewSections: {
        default: "Properties",
    },
    detailViewUseRawData: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Download', tooltip: 'Download Excel', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
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
        { text: 'Partner ID', dataIndex: 'partnerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'Account Code', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Account Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'My Kad No', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }, minWidth: 130, hidden: true  },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 130 },
        // { text: 'Bank Name', dataIndex: 'dbmbankname', filter: { type: 'string' }, minWidth: 130, hidden: true },
        {
            text: 'Type', dataIndex: 'type',
            filter: {
                type: 'combo',
                store: [
                    ['BUY_FPX', 'BUY_FPX'],
                    ['BUY_CONTAINER', 'BUY_CONTAINER'],
                    ['SELL', 'SELL'],

                    ['CONVERSION', 'CONVERSION'],
                    ['CONVERSION_FEE', 'CONVERSION_FEE'],
                    ['STORAGE_FEE', 'STORAGE_FEE'],
                    ['REFUND_DG', 'REFUND_DG'],
                    ['CREDIT', 'CREDIT'],
                    ['DEBIT', 'DEBIT'],
                    ['TRANSFER', 'TRANSFER'],
                    ['PROMO', 'PROMO'],
                    ['VAULT_IN', 'VAULT_IN'],
                    ['VAULT_OUT', 'VAULT_OUT'],
                    ['ACESELL', 'ACESELL'],
                    ['ACEBUY', 'ACEBUY'],
                    ['ACEREDEEM', 'ACEREDEEM'],
                ],
                renderer: function (value, rec) {
                    if (value == 'BUY_FPX') return 'BUY_FPX';
                    else if (value == 'BUY_CONTAINER') return 'BUY_CONTAINER';
                    else if (value == 'SELL') return 'SELL';
                    else if (value == 'CONVERSION') return 'CONVERSION';
                    else if (value == 'CONVERSION_FEE') return 'CONVERSION_FEE';
                    else if (value == 'STORAGE_FEE') return 'STORAGE_FEE';
                    else if (value == 'REFUND_DG') return 'REFUND_DG';
                    else if (value == 'CREDIT') return 'CREDIT';
                    else if (value == 'DEBIT') return 'DEBIT';
                    else if (value == 'TRANSFER') return 'TRANSFER';
                    else if (value == 'PROMO') return 'PROMO';
                    else if (value == 'VAULT_IN') return 'VAULT_IN';
                    else if (value == 'VAULT_OUT') return 'VAULT_OUT';
                    else if (value == 'ACESELL') return 'ACESELL';
                    else if (value == 'ACEBUY') return 'ACEBUY';
                    else if (value == 'ACEREDEEM') return 'ACEREDEEM';
                    else return 'UNIDENTIFIED';
                },
            },

        },
     
        // { text: 'Contact', dataIndex: 'contact', filter: { type: 'string' }, minWidth: 130 },
      
        {
            text: 'Order Gold Price', dataIndex: 'ordgoldprice', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        // {
        //     text: 'Xau Balance (g)', dataIndex: 'xaubalance', exportdecimal: 3, filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        // },
        {
            text: 'Amount In', dataIndex: 'amountin', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Amount Out', dataIndex: 'amountout', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        // {
        //     text: 'Amount Balance', dataIndex: 'amountbalance', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        // },
        {
            text: 'Credit', dataIndex: 'credit', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        {
            text: 'Debit', dataIndex: 'debit', exportdecimal: 2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        },
        { text: 'Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, },
        { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130, hidden: true },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],                    
                    ['1', 'Active'],
                    ['2', 'Failed'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Inactive';
                else if (value == '1') return 'Active';
                else if (value == '2') return 'Failed';
            },
        },
        { text: 'Trasaction Date', dataIndex: 'transactiondate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },

        // { text: 'Transfer On', dataIndex: 'transferon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        // { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 100, },
        // { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        // { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        // { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },

    ],
});
