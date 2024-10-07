Ext.define('snap.store.States', {
    extend: 'Ext.data.Store',

    alias: 'store.States',   
    model: 'snap.model.States',

    data: { items: [
        {"code":"01", "name":"JOHOR"},
		{"code":"02", "name":"KEDAH"},
		{"code":"03", "name":"KELANTAN"},
		{"code":"04", "name":"MELAKA"},
		{"code":"05", "name":"NEGERI SEMBILAN"},
		{"code":"06", "name":"PAHANG"},
		{"code":"07", "name":"PULAU PINANG"},
		{"code":"08", "name":"PERAK"},
		{"code":"09", "name":"PERLIS"},
		{"code":"10", "name":"SABAH"},
		{"code":"11", "name":"SARAWAK"},
		{"code":"12", "name":"SELANGOR"},
		{"code":"13", "name":"TERENGGANU"},
        {"code":"14", "name":"W.P. KUALA LUMPUR"},
        {"code":"15", "name":"W.P. LABUAN"},
        {"code":"16", "name":"W.P. PUTRAJAYA"},
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
