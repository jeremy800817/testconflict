Ext.define('snap.view.myaccountholder.MyAccountHolderKodimas', {
    extend: 'snap.view.myaccountholder.MyAccountHolderLoan',
    xtype: 'myaccountholderkodimasview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/kodimas/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=KODIMAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'KODIMAS',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
