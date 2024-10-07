Ext.define('snap.view.logistics.KtpLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'ktplogisticview',

    permissionRoot: '/root/ktp/logistic',
    partnerCode: 'KTP',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=KTP',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
