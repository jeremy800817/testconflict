Ext.define('snap.store.MyCompanyPaymentProvider', {
    extend: 'Ext.data.Store',

    alias: 'store.MyCompanyPaymentProvider',   
    model: 'snap.model.MyCompanyPaymentProvider',

    data: { items: [
        {"code":"", "name":"None"},
        {"code":"\\Snap\\api\\fpx\\M1Pay", "name":"M1Pay"},
        {"code":"\\Snap\\api\\fpx\\M1PayUAT", "name":"M1PayUAT"},
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
