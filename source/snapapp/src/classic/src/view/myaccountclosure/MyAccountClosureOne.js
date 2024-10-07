Ext.define('snap.view.myaccountclosure.MyAccountClosureOne', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myoneaccountclosureview',
    partnercode: 'ONE',
    permissionRoot: '/root/one/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
