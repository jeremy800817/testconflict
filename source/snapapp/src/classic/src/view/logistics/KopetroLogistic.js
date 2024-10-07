Ext.define('snap.view.logistics.KopetroLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'kopetrologisticview',

    permissionRoot: '/root/kopetro/logistic',
    partnerCode: 'KOPETRO',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KOPETRO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
