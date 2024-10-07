Ext.define('snap.view.logistics.PkbAffiLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'pkbaffilogisticview',

    permissionRoot: '/root/pkbaffi/logistic',
    partnerCode: 'PKBAFFI',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=PKBAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
