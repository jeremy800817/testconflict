Ext.define('snap.model.Replenishmentitems', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'string',
            name: 'serialno'
        },
        {
            type: 'string',
            name: 'productname'
        },
        {
            type: 'string',
            name: 'branchname'
        },
        {
            type: 'string',
            name: 'replenishmentno'
        },
    ]
});