Ext.define('snap.model.BuybackItems', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'string',
            name: 'partnername'
        },
        {
            type: 'string',
            name: 'partnerrefno'
        },
        {
            type: 'string',
            name: 'branchname'
        },
        {
            type: 'string',
            name: 'buybackno'
        },
    ]
});