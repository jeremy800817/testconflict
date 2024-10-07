Ext.define('snap.view.mypricealert.MyPriceAlertKtp', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'ktppricealertview',
    permissionRoot: '/root/ktp/pricealert',
    partnercode: 'KTP',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=KTP',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
