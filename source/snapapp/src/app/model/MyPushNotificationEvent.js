Ext.define('snap.model.MyPushNotificationEvent', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'string', name: 'eventtype'},
            {type: 'int', name: 'status'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
            
    ]
});
