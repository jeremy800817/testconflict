Ext.define('snap.model.Announcement', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'string', name: 'code'},
			{type: 'string', name: 'title'},
			{type: 'string', name: 'description'},
            {type: 'string', name: 'content'},
			{type: 'int', name: 'picture'},
            {type: 'int', name: 'rank'},
			{type: 'string', name: 'type'},
            {type: 'int', name: 'status'},
            {type: 'date', name: 'displaystarton'},
            {type: 'date', name: 'displayendon'},
			{type: 'int', name: 'timer'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},

            
    ]
});
