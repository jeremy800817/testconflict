Ext.define('snap.view.mypricealert.MyPriceAlertBsn', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'bsnpricealertview',
    permissionRoot: '/root/bsn/pricealert',
    partnercode: 'BSN',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
