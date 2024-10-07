//
Ext.define('snap.model.Eventsubscriber', {
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
            type: 'string',
            name: 'receiver'
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
