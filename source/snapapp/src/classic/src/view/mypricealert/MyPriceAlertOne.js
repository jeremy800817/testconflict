Ext.define('snap.view.mypricealert.MyPriceAlertOne', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'onepricealertview',
    permissionRoot: '/root/one/pricealert',
    partnercode: 'ONE',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
