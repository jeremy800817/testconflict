Ext.define('snap.store.MyMonthlyStorageFee', {
    extend: 'snap.store.Base',
    model: 'snap.model.MyMonthlyStorageFee',
    alias: 'store.MyMonthlyStorageFee',
    storeId:'mymonthlystoragefee',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=mymonthlystoragefee&action=list',
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});


