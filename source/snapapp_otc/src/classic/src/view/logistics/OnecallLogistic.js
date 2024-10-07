Ext.define('snap.view.logistics.OnecallLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'onecalllogisticview',

    permissionRoot: '/root/onecall/logistic',
    partnerCode: 'ONECALL',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
