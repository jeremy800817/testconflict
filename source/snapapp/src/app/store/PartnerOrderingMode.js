Ext.define('snap.store.PartnerOrderingMode', {
    extend: 'Ext.data.Store',

    alias: 'store.PartnerOrderingMode',   
    model: 'snap.model.PartnerOrderingMode',

    data: { items: [
        {"mode":"None", "name":"None"},
        {"mode":"Web", "name":"Web"}, 
		{"mode":"API", "name":"API"},
		{"mode":"Both", "name":"Both"},		
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
