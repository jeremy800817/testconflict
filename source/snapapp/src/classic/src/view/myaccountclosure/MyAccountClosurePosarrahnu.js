Ext.define('snap.view.myaccountclosure.MyAccountClosurePosarrahnu', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myposarrahnuaccountclosureview',
    partnercode: 'POSARRAHNU',
    permissionRoot: '/root/posarrahnu/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
