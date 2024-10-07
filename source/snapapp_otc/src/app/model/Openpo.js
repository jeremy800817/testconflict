Ext.define('snap.model.Openpo', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'docNum'
        },
        {
            type: 'string',
            name: 'cardCode'
        },
        {
            type: 'string',
            name: 'docEntry'
        },
        {
            type: 'string',
            name: 'docTotal'
        },
    ]
});