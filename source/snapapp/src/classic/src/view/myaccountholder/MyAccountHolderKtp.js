Ext.define('snap.view.myaccountholder.MyAccountHolderKtp', {
    extend: 'snap.view.myaccountholder.MyAccountHolderLoan',
    xtype: 'myaccountholderktpview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/ktp/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=KTP',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'KTP',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
