Ext.define('snap.view.logistics.NubexLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'nubexlogisticview',

    permissionRoot: '/root/nubex/logistic',
    partnerCode: 'NUBEX',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
