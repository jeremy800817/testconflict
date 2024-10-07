Ext.define('snap.view.logistics.HopeLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'hopelogisticview',

    permissionRoot: '/root/hope/logistic',
    partnerCode: 'HOPE',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
