Ext.define('snap.view.mypricealert.MyPriceAlertPosarrahnu', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'posarrahnupricealertview',
    permissionRoot: '/root/posarrahnu/pricealert',
    partnercode: 'POSARRAHNU',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
