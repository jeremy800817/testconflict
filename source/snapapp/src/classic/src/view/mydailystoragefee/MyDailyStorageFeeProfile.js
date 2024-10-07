Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeProfile', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mydailystoragefeeprofileview',
    permissionRoot: '/root/bmmb/profile',

    columns: [
        { text: 'Date & Time', dataIndex: 'calculatedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Cust Xau Holding (g)', dataIndex: 'ledcurrentxau', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'XAU Charge', dataIndex: 'xau', filter: { type: 'string' }, minWidth: 130, flex: 1 },
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
