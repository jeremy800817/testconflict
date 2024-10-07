Ext.define('snap.view.myaccountclosure.MyAccountClosure', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myaccountclosureview',
    requires: [
        'snap.store.MyAccountClosure',
        'snap.model.MyAccountClosure',
        'snap.view.myaccountclosure.MyAccountClosureController',
        'snap.view.myaccountclosure.MyAccountClosureModel'
    ],
    permissionRoot: '/root/bmmb/accountclosure',
    store: { type: 'MyAccountClosure' },
    controller: 'myaccountclosure-myaccountclosure',
    viewModel: {
        type: 'myaccountclosure-myaccountclosure'
    },
    detailViewWindowHeight: 500,
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
            text: 'Print', tooltip: 'Print Report', iconCls: 'x-fa fa-print', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        }
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
        {
            text: 'No.', dataIndex: 'rowIndex', sortable: false,  flex: 1,           // other config you need..
            renderer: function (value, metaData, record, rowIndex) {
                return rowIndex + 1;
            }
        },
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
        { text: 'Code', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Full Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        // { text: 'Reason', dataIndex: 'locreason', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Transaction Ref', dataIndex: 'transactionrefno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Requested on', dataIndex: 'requestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Closed on', dataIndex: 'closedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
        {
            text: 'Status', dataIndex: 'status', minWidth: 100,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Approved'],
                    ['2', 'Rejected'],
                    ['3', 'In Progress'],
                    ['4', 'Reactivated'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending';
                else if (value == '1') return 'Approved';
                else if (value == '2') return 'Rejected';
                else if (value == '3') return 'In Progress';
                else if (value == '4') return 'Reactivated';
            },
        },
    ],
});
