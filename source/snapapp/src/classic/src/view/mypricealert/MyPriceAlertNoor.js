Ext.define('snap.view.mypricealert.MyPriceAlertNoor', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'noorpricealertview',
    permissionRoot: '/root/noor/pricealert',
    partnercode: 'NOOR',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
