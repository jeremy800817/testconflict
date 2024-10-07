Ext.define('snap.view.mypricealert.MyPriceAlertKgoldAffi', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'kgoldaffipricealertview',
    permissionRoot: '/root/kgoldaffi/pricealert',
    partnercode: 'KGOLDAFFI',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KGOLDAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
