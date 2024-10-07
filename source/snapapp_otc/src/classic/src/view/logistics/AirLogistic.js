Ext.define('snap.view.logistics.AirLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'airlogisticview',

    permissionRoot: '/root/air/logistic',
    partnerCode: 'AIR',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
