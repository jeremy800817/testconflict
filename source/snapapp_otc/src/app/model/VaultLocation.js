Ext.define('snap.model.VaultLocation', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'string', name: 'name'},
			{type: 'int', name: 'owner'},
            {type: 'int', name: 'minimumlevel'},
            {type: 'int', name: 'reorderlevel'},
            {type: 'int', name: 'defaultlocation'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},

            {type: 'string', name: 'partnername'},
            {type: 'string', name: 'ownername'},

    ]
});
