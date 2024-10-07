Ext.define('snap.store.MyPayoutProvider', {
    extend: 'Ext.data.Store',

    alias: 'store.MyPayoutProvider',   
    model: 'snap.model.MyPayoutProvider',

    data: { items: [
        {"code":"", "name":"None"},
        {"code":"\\Snap\\api\\payout\\CompanyManualPayout", "name":"Manual"},        
    ]},

    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
