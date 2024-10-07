Ext.define('snap.view.mypricealert.MyPriceAlertKodimas', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'kodimaspricealertview',
    permissionRoot: '/root/kodimas/pricealert',
    partnercode: 'KODIMAS',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KODIMAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
