Ext.define('snap.view.mypricealert.MyPriceAlertKasih', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'kasihpricealertview',
    permissionRoot: '/root/kasih/pricealert',
    partnercode: 'KASIH',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KASIH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
