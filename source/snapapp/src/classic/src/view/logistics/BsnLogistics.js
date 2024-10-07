Ext.define('snap.view.logistics.BsnLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'bsnlogisticview',

    permissionRoot: '/root/bsn/logistic',
    partnerCode: 'BSN',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
