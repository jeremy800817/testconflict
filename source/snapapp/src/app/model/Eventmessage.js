//
Ext.define('snap.model.Eventmessage', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'id'
        },
        {
            type: 'string',
            name: 'code'
        },
        {
            type: 'string',
            name: 'replace'
        },
        {
            type: 'string',
            name: 'subject'
        },
        {
            type: 'string',
            name: 'content'
        },
        {
            type: 'date',
            name: 'createdon'
        },
		{
            type: 'date',
            name: 'modifiedon'
        },
        {
            type: 'int',
            name: 'createdby'
        },
        {
            type: 'int',
            name: 'modifiedby'
        },
        {
            type: 'int',
            name: 'status',
            defaultValue: 1
        }
    ]
});
