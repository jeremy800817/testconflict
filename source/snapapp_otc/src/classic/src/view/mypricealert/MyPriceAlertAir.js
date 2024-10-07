Ext.define('snap.view.mypricealert.MyPriceAlertAir', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'airpricealertview',
    permissionRoot: '/root/air/pricealert',
    partnercode: 'AIR',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
