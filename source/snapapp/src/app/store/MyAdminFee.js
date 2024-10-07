Ext.define('snap.store.MyAdminFee', {
    extend: 'snap.store.Base',
    model: 'snap.model.MyAdminFee',
    alias: 'store.MyAdminFee',
    storeId:'myadminfee',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=myadminfee&action=list',
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});


