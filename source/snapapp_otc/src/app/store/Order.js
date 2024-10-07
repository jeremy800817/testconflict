Ext.define('snap.store.Order', {
    extend: 'snap.store.Base',
    model: 'snap.model.Order',
    alias: 'store.Order',
    autoLoad: true,
    sorters: [{
        property: 'id',
        direction: 'DESC'
    }]
});
