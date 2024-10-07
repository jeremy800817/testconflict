Ext.define('snap.view.logistics.RedLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'redlogisticview',

    permissionRoot: '/root/red/logistic',
    partnerCode: 'RED',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
