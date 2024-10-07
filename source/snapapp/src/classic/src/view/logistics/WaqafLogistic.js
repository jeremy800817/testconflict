Ext.define('snap.view.logistics.WaqafLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'waqaflogisticview',

    permissionRoot: '/root/waqaf/logistic',
    partnerCode: 'WAQAF',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=WAQAF',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
