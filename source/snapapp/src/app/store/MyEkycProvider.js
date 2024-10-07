Ext.define('snap.store.MyEkycProvider', {
    extend: 'Ext.data.Store',

    alias: 'store.MyEkycProvider',   
    model: 'snap.model.MyEkycProvider',

    data: { items: [
        {"code":"", "name":"None"},
        {"code":"\\Snap\\util\\ekyc\\Innov8tifProvider", "name":"Innov8tif"},
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
