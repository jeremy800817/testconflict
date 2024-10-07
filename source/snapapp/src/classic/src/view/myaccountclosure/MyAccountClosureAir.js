Ext.define('snap.view.myaccountclosure.MyAccountClosureAir', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myairaccountclosureview',
    partnercode: 'AIR',
    permissionRoot: '/root/air/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
