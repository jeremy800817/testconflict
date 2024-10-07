Ext.define('snap.view.mypricealert.MyPriceAlertIgold', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'igoldpricealertview',
    permissionRoot: '/root/igold/pricealert',
    partnercode: 'IGOLD',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
