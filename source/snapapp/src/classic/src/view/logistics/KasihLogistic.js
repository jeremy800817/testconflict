Ext.define('snap.view.logistics.KasihLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'kasihlogisticview',

    permissionRoot: '/root/kasih/logistic',
    partnerCode: 'KASIH',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KASIH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
