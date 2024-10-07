Ext.define('snap.view.mytransfergold.SenderSearchResult', {
    extend: 'snap.view.myaccountholder.MyAccountHolder',
    xtype: 'sendersearchresultview',

    selType: 'rowmodel',
    partnerCode: PROJECTBASE,
    // Same thing but replace dataindexes
    toolbarItems: [
        'detail', '|', 'filter', '|',
        { reference: 'profileBtn', handler: 'onViewAccountHolder', text: 'CIF', showToolbarItemText: true, itemId: 'profileBtn', tooltip: 'Account Holder Profile', viewType: 'myprofileview', iconCls: 'x-fa fa-user', validSelection: 'single', style:'display:none' },
        { reference: 'approvePep', showToolbarItemText: true, itemId: 'approvePep', tooltip: 'Approve PEP', iconCls: 'x-fa fa-check-square', handler: 'approvePep', validSelection: 'single', style:'display:none' },
        { reference: 'suspendBtn', showToolbarItemText: true, itemId: 'suspendBtn', tooltip: 'Suspend', iconCls: 'x-fa fa-lock', handler: 'onSuspendAccountHolder', validSelection: 'single', style:'display:none' },
        { reference: 'unsuspendBtn', showToolbarItemText: true, itemId: 'unsuspendBtn', tooltip: 'Unsuspend', iconCls: 'x-fa fa-unlock', handler: 'onUnsuspendAccountHolder', validSelection: 'single', style:'display:none' },
        { reference: 'closeBtn', showToolbarItemText: true, itemId: 'closeBtn', tooltip: 'Close', iconCls: 'x-fa fa-eraser', handler: 'onCloseAccountHolder', validSelection: 'single', style:'display:none' },
        { reference: 'approveEkyc', showToolbarItemText: true, itemId: 'approveEkyc', tooltip: 'Approve EKYC', iconCls: 'x-fa fa-thumbs-up', handler: 'approveEkyc', validSelection: 'single', style:'display:none' },
        { reference: 'displayIdentityPhoto', showToolbarItemText: true, itemId: 'displayIdentityPhoto', tooltip: 'Display Identity Photo', iconCls: 'x-fa fa-image', handler: 'displayIdentityPhoto', validSelection: 'single', style:'display:none' },
    ],
    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
        { text: 'Amount Balance', dataIndex: 'amountbalance',exportdecimal:2, filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00') },
        { text: PROJECTBASE === 'BSN'? 'Gold Account No' : 'Account Code', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'My Kad No', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Phone Number', dataIndex: 'phoneno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Occupation Category', dataIndex: 'occupationcategory', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Investment Made', dataIndex: 'investmentmade',   filter: {
            type: 'combo',
            store: [
                ['0', 'No'],
                ['1', 'Yes'],
            ],
        },
        renderer: function (value, rec) {
            if (value == '0') return 'No';
            else if (value == '1') return 'Yes';
            else return 'Unidentified';
        }, hidden: true, minWidth: 130, flex: 1, },
        { text: 'Xau Balance', dataIndex: 'xaubalance', exportdecimal:3, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000') },
        {
            text: 'Status', dataIndex: 'status', minWidth: 100,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                    ['2', 'Suspended'],
                    ['4', 'Blacklisted'],
                    ['5', 'Closed'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span data-qtitle="Inactive" data-qwidth="200" '+
                'data-qtip="Account Pending Email Activation">'+
                 "Inactive" +'</span>';
                else if (value == '1') return '<span data-qtitle="Active" data-qwidth="200" '+
                'data-qtip="Active Accounts">'+
                 "Active" +'</span>';
                else if (value == '2') return '<span data-qtitle="Suspended" data-qwidth="200" '+
                'data-qtip="Accounts Pending Closure Approval">'+
                 "Suspended" +'</span>';
                else if (value == '4') return '<span data-qtitle="Blacklisted" data-qwidth="200" '+
                'data-qtip="Blacklisted Accounts">'+
                 "Blacklisted" +'</span>';
                else if (value == '5') return '<span data-qtitle="Closed" data-qwidth="200" '+
                'data-qtip="Accounts Successfully Closed">'+
                 "Closed" +'</span>';
                else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                'data-qtip="Unknown Status">'+
                 "Unidentified" +'</span>';
            },
            // renderer: function (value, rec) {
            //     if (value == '0') return 'Inactive';
            //     else if (value == '1') return 'Active';
            //     else if (value == '2') return 'Suspended';
            //     else if (value == '4') return 'Blacklisted';
            //     else if (value == '5') return 'Closed';
                    
            //     else return 'Unidentified';
            // },
        },
    ],

});
