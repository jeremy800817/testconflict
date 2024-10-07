Ext.define('snap.model.ApiGoldRequest', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'string', name: 'partnerrefid'},
			{type: 'string', name: 'apiversion'},
            {type: 'int', name: 'quantity'},
            {type: 'string', name: 'reference'},
			{type: 'date', name: 'timestamp'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
    ]
});
