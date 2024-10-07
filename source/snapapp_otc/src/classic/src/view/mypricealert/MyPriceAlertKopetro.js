Ext.define('snap.view.mypricealert.MyPriceAlertKopetro', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'kopetropricealertview',
    permissionRoot: '/root/kopetro/pricealert',
    partnercode: 'KOPETRO',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KOPETRO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
