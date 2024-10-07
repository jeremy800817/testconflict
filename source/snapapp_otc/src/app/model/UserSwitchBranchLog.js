Ext.define('snap.model.UserSwitchBranchLog', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'userid'},
			{type: 'string', name: 'username'},
			{type: 'int', name: 'frompartnerid'},
			{type: 'string', name: 'frompartnername'},
            {type: 'int', name: 'topartnerid'},
			{type: 'string', name: 'topartnername'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'string', name: 'createdbyname'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'string', name: 'modifiedbyname'},
			{type: 'int', name: 'status'},
    ]
});
