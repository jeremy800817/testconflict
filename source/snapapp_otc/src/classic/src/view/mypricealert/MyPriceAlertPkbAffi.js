Ext.define('snap.view.mypricealert.MyPriceAlertPkbAffi', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'pkbaffipricealertview',
    permissionRoot: '/root/pkbaffi/pricealert',
    partnercode: 'PKBAFFI',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=PKBAFFI',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
