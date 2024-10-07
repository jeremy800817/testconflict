Ext.define('snap.view.mypricealert.MyPriceAlertGo', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'gopricealertview',
    permissionRoot: '/root/go/pricealert',
    partnercode: 'GO',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
