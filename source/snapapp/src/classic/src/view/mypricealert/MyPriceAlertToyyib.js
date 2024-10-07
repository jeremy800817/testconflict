Ext.define('snap.view.mypricealert.MyPriceAlertToyyib', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'toyyibpricealertview',
    permissionRoot: '/root/toyyib/pricealert',
    partnercode: 'TOYYIB',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
