//
Ext.define('snap.model.Eventtrigger', {
    extend: 'snap.model.Base',
    fields: [
        {
            type: 'int',
            name: 'id'
        },
        {
            type: 'int',
            name: 'grouptypeid'
        },
        {
            type: 'int',
            name: 'moduleid'
        },
        {
            type: 'int',
            name: 'actionid'
        },
        {
            type: 'string',
            name: 'matcherclass'
        },
        {
            type: 'string',
            name: 'processorclass'
        },
        {
            type: 'int',
            name: 'messageid'
        },
        {
            type: 'string',
            name: 'observableclass'
        },
        {
            type: 'int',
            name: 'oldstatus'
        },
        {
            type: 'int',
            name: 'newstatus'
        },
        {
            type: 'string',
            name: 'objectclass'
        },
        {
            type: 'string',
            name: 'storetolog'
        },
        {
            type: 'string',
            name: 'groupidfieldname'
        },
        {
            type: 'string',
            name: 'evalcode'
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
