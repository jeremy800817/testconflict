Ext.define('snap.store.PartnerCalculatorMode', {
    extend: 'Ext.data.Store',

    alias: 'store.PartnerCalculatorMode',   
    model: 'snap.model.PartnerCalculatorMode',

    data: { items: [
        {"mode":"GTP", "name":"GTP"},
        {"mode":"MBB", "name":"MBB"}, 
        {"mode":"BMMB", "name":"BMMB"}, 
		
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
