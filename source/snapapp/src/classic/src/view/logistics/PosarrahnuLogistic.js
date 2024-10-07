Ext.define('snap.view.logistics.PosarrahnuLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'posarrahnulogisticview',

    permissionRoot: '/root/posarrahnu/logistic',
    partnerCode: 'POSARRAHNU',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
