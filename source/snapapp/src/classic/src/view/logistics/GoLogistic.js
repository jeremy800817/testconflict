Ext.define('snap.view.logistics.GoLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'gologisticview',

    permissionRoot: '/root/go/logistic',
    partnerCode: 'GO',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }

});
