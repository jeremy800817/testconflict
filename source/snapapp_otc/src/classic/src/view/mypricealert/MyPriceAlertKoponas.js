Ext.define('snap.view.mypricealert.MyPriceAlertKoponas', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'koponaspricealertview',
    permissionRoot: '/root/koponas/pricealert',
    partnercode: 'KOPONAS',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KOPONAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
