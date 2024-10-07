Ext.define('snap.view.logistics.BursaLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'bursalogisticview',

    permissionRoot: '/root/bursa/logistic',
    partnerCode: 'BURSA',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
