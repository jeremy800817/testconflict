Ext.define('snap.view.myaccountholder.MyAccountHolderAir', {
    extend: 'snap.view.myaccountholder.MyAccountHolder',
    xtype: 'myaccountholderairview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/air/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'AIR',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
