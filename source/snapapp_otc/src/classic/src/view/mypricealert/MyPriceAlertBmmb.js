Ext.define('snap.view.mypricealert.MyPriceAlertBmmb', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'bmmbpricealertview',
    permissionRoot: '/root/bmmb/pricealert',
    partnercode: 'BMMB',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
