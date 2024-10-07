Ext.define('snap.model.PriceDelay', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'int', name: 'pricesource' },
        { type: 'int', name: 'delay' },
        { type: 'date', name: 'createdon' },
        { type: 'string', name: 'createdby' },

    ]
});
