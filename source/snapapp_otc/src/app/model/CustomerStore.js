Ext.define('snap.model.CustomerStore', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'id'
        },
        {
            type: 'string',
            name: 'name'
        },
        {
            type: 'string',
            name: 'sapcode'
        },
    ]
});