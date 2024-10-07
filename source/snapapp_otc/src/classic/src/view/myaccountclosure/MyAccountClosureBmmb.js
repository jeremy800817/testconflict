Ext.define('snap.view.myaccountclosure.MyAccountClosureBmmb', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mybmmbaccountclosureview',
    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
