Ext.define('snap.store.PartnerTypes', {
    extend: 'Ext.data.Store',

    alias: 'store.PartnerTypes',   
    model: 'snap.model.PartnerTypes',

    data: { items: [
        {"type":"Customer", "name":"Customer"},
        {"type":"Referal", "name":"Referal"},       
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
