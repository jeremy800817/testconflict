Ext.define('snap.view.mypricealert.MyPriceAlertWaqaf', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'waqafpricealertview',
    permissionRoot: '/root/waqaf/pricealert',
    partnercode: 'WAQAF',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=WAQAF',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
