Ext.define('snap.view.mypricealert.MyPriceAlertNubex', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'nubexpricealertview',
    permissionRoot: '/root/nubex/pricealert',
    partnercode: 'NUBEX',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
