Ext.define('snap.view.mypricealert.MyPriceAlertKopttr', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'kopttrpricealertview',
    permissionRoot: '/root/kopttr/pricealert',
    partnercode: 'KOPTTR',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KOPTTR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
