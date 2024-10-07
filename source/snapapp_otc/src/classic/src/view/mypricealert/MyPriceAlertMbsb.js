Ext.define('snap.view.mypricealert.MyPriceAlertMbsb', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'mbsbpricealertview',
    permissionRoot: '/root/mbsb/pricealert',
    partnercode: 'MBSB',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
