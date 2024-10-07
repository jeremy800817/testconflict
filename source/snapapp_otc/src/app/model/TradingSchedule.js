Ext.define('snap.model.TradingSchedule', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'categoryid'},
			{type: 'string', name: 'type'},
			{type: 'date', name: 'startat'},
			{type: 'date', name: 'endat'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status', defaultValue: 1},    	
    ]
});

