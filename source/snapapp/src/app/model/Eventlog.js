//
Ext.define('snap.model.Eventlog', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'id'
        },
        {
            type: 'int',
            name: 'triggerid'
        },
        {
            type: 'int',
            name: 'groupid'
        },
        {
            type: 'int',
            name: 'objectid'
        },
        {
            type: 'string',
            name: 'reference'
        },
        {
            type: 'string',
            name: 'subject'
        },
        {
            type: 'string',
            name: 'log'
        },
        {
            type: 'string',
            name: 'sendto'
        },
        {
            type: 'date',
            name: 'sendon'
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
