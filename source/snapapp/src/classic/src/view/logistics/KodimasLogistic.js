Ext.define('snap.view.logistics.KodimasLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'kodimaslogisticview',

    permissionRoot: '/root/kodimas/logistic',
    partnerCode: 'KODIMAS',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KODIMAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
