Ext.define('snap.model.LogisticTracker', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'string', name: 'apiversion'},
			{type: 'string', name: 'itemtype'},
			{type: 'int', name: 'itemid'},
			{type: 'int', name: 'senderid'},
            {type: 'string', name: 'senderref'},
			{type: 'date', name: 'sendon'},
			{type: 'int', name: 'sendby'},
            {type: 'date', name: 'receivedon'},
			{type: 'string', name: 'receivedperson'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},


			{type: 'string', name: 'partnername'},
            {type: 'string', name: 'itemname'},
            {type: 'string', name: 'sendername'},
			{type: 'string', name: 'partnercode'},
			{type: 'string', name: 'createdbyname'},
			{type: 'string', name: 'modifiedbyname'},
    ]
});
