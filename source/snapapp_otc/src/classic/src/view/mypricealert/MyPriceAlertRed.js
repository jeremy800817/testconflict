Ext.define('snap.view.mypricealert.MyPriceAlertRed', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'redpricealertview',
    permissionRoot: '/root/red/pricealert',
    partnercode: 'RED',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
