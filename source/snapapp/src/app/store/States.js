Ext.define('snap.store.States', {
    extend: 'Ext.data.Store',

    alias: 'store.States',   
    model: 'snap.model.States',

    data: { items: [
        {"code":"Kuala Lumpur", "name":"Kuala Lumpur"},
        {"code":"Labuan", "name":"Labuan"},
        {"code":"Putrajaya", "name":"Johor"},
		{"code":"Johor", "name":"Johor"},
		{"code":"Kedah", "name":"Kedah"},
		{"code":"Kelantan", "name":"Kelantan"},
		{"code":"Melaka", "name":"Melaka"},
		{"code":"Negeri Sembilan", "name":"Negeri Sembilan"},
		{"code":"Pahang", "name":"Pahang"},
		{"code":"Perak", "name":"Perak"},
		{"code":"Perlis", "name":"Perlis"},
		{"code":"Penang", "name":"Penang"},
		{"code":"Sabah", "name":"Sabah"},
		{"code":"Sarawak", "name":"Sarawak"},
		{"code":"Selangor", "name":"Selangor"},
		{"code":"Terengganu", "name":"Terengganu"},
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
