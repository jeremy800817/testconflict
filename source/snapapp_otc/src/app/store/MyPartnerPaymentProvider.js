Ext.define('snap.store.MyPartnerPaymentProvider', {
    extend: 'Ext.data.Store',

    alias: 'store.MyPartnerPaymentProvider',   
    model: 'snap.model.MyPartnerPaymentProvider',

    data: { items: [
        {"code": "", "name": "None" },
        {"code":"\\Snap\\api\\wallet\\GoPayz", "name":"GoPayz"},
        {"code":"\\Snap\\api\\wallet\\GoPayzUAT", "name":"GoPayzUAT"},
        {"code":"\\Snap\\api\\wallet\\MCash", "name":"MCash"},
        {"code":"\\Snap\\api\\wallet\\MCashUAT", "name":"MCashUAT"},
        {"code":"\\Snap\\api\\wallet\\OneCall", "name":"OneCall"},
        {"code":"\\Snap\\api\\wallet\\OneCallUAT", "name":"OneCallUAT"},
        {"code":"\\Snap\\api\\wallet\\Toyyib", "name":"Toyyib"},
        {"code":"\\Snap\\api\\wallet\\ToyyibUAT", "name":"ToyyibUAT"},
        {"code":"\\Snap\\api\\wallet\\HopeGold", "name":"HopeGold"},
        {"code":"\\Snap\\api\\wallet\\HopeGoldUAT", "name":"HopeGoldUAT"},
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
