Ext.define('snap.view.mypricealert.MyPriceAlertBursa', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'bursapricealertview',
    permissionRoot: '/root/bursa/pricealert',
    partnercode: 'BURSA',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
