Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeProfile', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mymonthlystoragefeeprofileview',
    requires: [
        'snap.store.MyMonthlyStorageFee',
        'snap.model.MyMonthlyStorageFee',
        'snap.view.mymonthlystoragefee.MyMonthlyStorageFeeController',
        'snap.view.mymonthlystoragefee.MyMonthlyStorageFeeModel'
    ],
    permissionRoot: '/root/bmmb/profile',
    store: { type: 'MyMonthlyStorageFee' },
    controller: 'mymonthlystoragefee-mymonthlystoragefee',

    viewModel: {
        type: 'mymonthlystoragefee-mymonthlystoragefee'
    },
    columns: [
        { text: 'Date & Time', dataIndex: 'chargedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Ace Ref', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Cust Xau Holding (g)', dataIndex: 'ledcurrentxau', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Gold Price RM/g', dataIndex: 'price', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'XAU Charge', dataIndex: 'xau', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Amount', dataIndex: 'amount', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        {
            text: 'Status', dataIndex: 'status', minWidth: 100,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Inactive';
                else if (value == '1') return 'Active';
                else return 'Unidentified';
            },
        },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y/m/d H:i:s',  filter: {type: 'string'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s',  filter: {type: 'string'  }, inputType: 'hidden', hidden: true, flex: 1 },
    ],
});
