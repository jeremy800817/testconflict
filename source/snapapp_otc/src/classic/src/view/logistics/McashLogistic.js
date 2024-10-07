Ext.define('snap.view.logistics.MCashLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'mcashlogisticview',

    permissionRoot: '/root/mcash/logistic',
    partnerCode: 'MCASH',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
