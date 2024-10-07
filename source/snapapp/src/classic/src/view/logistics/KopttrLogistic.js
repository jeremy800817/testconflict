Ext.define('snap.view.logistics.KopttrLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'kopttrlogisticview',

    permissionRoot: '/root/kopttr/logistic',
    partnerCode: 'KOPTTR',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KOPTTR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
