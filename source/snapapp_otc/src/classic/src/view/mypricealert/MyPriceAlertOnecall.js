Ext.define('snap.view.mypricealert.MyPriceAlertOnecall', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'onecallpricealertview',
    permissionRoot: '/root/onecall/pricealert',
    partnercode: 'ONECALL',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
