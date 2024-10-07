Ext.define('snap.view.myaccountholder.MyAccountHolderMbsb', {
    extend: 'snap.view.myaccountholder.MyAccountHolder',
    xtype: 'myaccountholdermbsbview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/mbsb/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'MBSB',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
