Ext.define('snap.model.Role', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'id'
        },
        {
            type: 'string',
            name: 'title'
        },
        {
            type: 'string',
            name: 'description'
        },
        {
            type: 'string',
            name: 'permissions'
        }
    ]
});
