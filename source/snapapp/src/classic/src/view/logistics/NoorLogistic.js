Ext.define('snap.view.logistics.NoorLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'noorlogisticview',

    permissionRoot: '/root/noor/logistic',
    partnerCode: 'NOOR',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
