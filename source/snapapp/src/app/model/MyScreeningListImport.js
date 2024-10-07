Ext.define('snap.model.MyScreeningListImport', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
        	{type: 'string', name: 'sourcetype'},
        	{type: 'string', name: 'url'},
        	{type: 'int', name: 'status'},
			{type: 'date', name: 'createdon'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'createdby'},
			{type: 'int', name: 'modifiedby'},

			{type: 'string', name: 'createdbyname'},
			{type: 'string', name: 'modifiedbyname'},
    ]
});
