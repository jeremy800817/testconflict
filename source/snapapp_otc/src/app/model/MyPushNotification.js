Ext.define('snap.model.MyPushNotification', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'string', name: 'eventtype'},
			{type: 'string', name: 'code'},
			{type: 'string', name: 'sound'},
			{type: 'string', name: 'icon'},
            {type: 'int', name: 'rank'},
			{type: 'date', name: 'validfrom'},
            {type: 'date', name: 'validto'},
            {type: 'int', name: 'status'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
            
    ]
});
