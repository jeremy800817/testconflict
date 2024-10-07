Ext.define('snap.view.logistics.OneLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'onelogisticview',

    permissionRoot: '/root/one/logistic',
    partnerCode: 'ONE',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
