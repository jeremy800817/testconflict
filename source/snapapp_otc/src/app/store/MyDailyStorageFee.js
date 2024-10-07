Ext.define('snap.store.MyDailyStorageFee', {
    extend: 'snap.store.Base',
    model: 'snap.model.MyDailyStorageFee',
    alias: 'store.MyDailyStorageFee',
    storeId:'mydailystoragefee',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=mydailystoragefee&action=list',
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});


