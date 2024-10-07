Ext.define('snap.view.logistics.IgoldLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'igoldlogisticview',

    permissionRoot: '/root/igold/logistic',
    partnerCode: 'IGOLD',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
