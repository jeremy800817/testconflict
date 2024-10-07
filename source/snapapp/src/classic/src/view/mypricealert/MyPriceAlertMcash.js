Ext.define('snap.view.mypricealert.MyPriceAlertMcash', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'mcashpricealertview',
    permissionRoot: '/root/mcash/pricealert',
    partnercode: 'MCASH',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
