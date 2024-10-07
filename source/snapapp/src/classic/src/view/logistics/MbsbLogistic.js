Ext.define('snap.view.logistics.MbsbLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'mbsblogisticview',

    permissionRoot: '/root/mbsb/logistic',
    partnerCode: 'MBSB',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
