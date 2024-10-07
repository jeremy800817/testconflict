Ext.define('snap.view.logistics.KoponasLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'koponaslogisticview',

    permissionRoot: '/root/koponas/logistic',
    partnerCode: 'KOPONAS',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KOPONAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
