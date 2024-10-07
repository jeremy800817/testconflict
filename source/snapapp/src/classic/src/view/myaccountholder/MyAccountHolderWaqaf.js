Ext.define('snap.view.myaccountholder.MyAccountHolderWaqaf', {
    extend: 'snap.view.myaccountholder.MyAccountHolderLoan',
    xtype: 'myaccountholderwaqafview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/waqaf/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=WAQAF',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'WAQAF',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
