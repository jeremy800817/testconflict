Ext.define('snap.view.logistics.KgoldAffiLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'kgoldaffilogisticview',

    permissionRoot: '/root/kgoldaffi/logistic',
    partnerCode: 'KGOLDAFFI',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KGOLDAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
