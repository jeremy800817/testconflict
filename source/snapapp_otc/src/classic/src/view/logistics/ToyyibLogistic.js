Ext.define('snap.view.logistics.ToyyibLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'toyyiblogisticview',

    permissionRoot: '/root/toyyib/logistic',
    partnerCode: 'TOYYIB',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
