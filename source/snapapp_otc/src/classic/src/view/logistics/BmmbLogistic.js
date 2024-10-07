Ext.define('snap.view.logistics.BmmbLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'bmmblogisticview',

    permissionRoot: '/root/bmmb/logistic',
    partnerCode: 'BMMB',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
