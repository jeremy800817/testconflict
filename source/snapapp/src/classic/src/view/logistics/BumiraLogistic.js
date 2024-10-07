Ext.define('snap.view.logistics.BumiraLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'bumiralogisticview',

    permissionRoot: '/root/bumira/logistic',
    partnerCode: 'BUMIRA',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=BUMIRA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
