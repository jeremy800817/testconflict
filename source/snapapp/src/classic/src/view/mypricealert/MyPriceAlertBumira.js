Ext.define('snap.view.mypricealert.MyPriceAlertBumira', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'bumirapricealertview',
    permissionRoot: '/root/bumira/pricealert',
    partnercode: 'BUMIRA',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=BUMIRA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
