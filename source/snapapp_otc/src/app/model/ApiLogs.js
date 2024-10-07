Ext.define('snap.model.ApiLogs', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'string', name: 'type'},
			{type: 'string', name: 'fromip'},
			{type: 'int', name: 'systeminitiate'},
            {type: 'string', name: 'requestdata'},
            {type: 'string', name: 'responsedata'},
            {type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},

            {type: 'string', name: 'createdbyname'},
			{type: 'string', name: 'modifiedbyname'}
    ]
});
