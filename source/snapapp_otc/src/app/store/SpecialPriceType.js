Ext.define('snap.store.SpecialPriceType', {
    extend: 'Ext.data.Store',

    alias: 'store.SpecialPriceType',   
    model: 'snap.model.SpecialPriceType',

    data: { items: [
        {"id":"NONE", "name":"NONE"},
        {"id":"AMOUNT", "name":"AMOUNT"}, 
		{"id":"GRAM", "name":"GRAM"},	
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
