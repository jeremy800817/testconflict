Ext.define('snap.view.myaccountclosure.MyAccountClosureNubex', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mynubexaccountclosureview',
    partnercode: 'NUBEX',
    permissionRoot: '/root/nubex/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
