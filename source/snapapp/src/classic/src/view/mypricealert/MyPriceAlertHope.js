Ext.define('snap.view.mypricealert.MyPriceAlertHope', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'hopepricealertview',
    permissionRoot: '/root/hope/pricealert',
    partnercode: 'HOPE',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
