Ext.define('snap.model.VaultItemTrans', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
            {type: 'int', name: 'partnerid'},
            {type: 'string', name: 'type'},
            {type: 'string', name: 'documentno'},
            {type: 'date', name: 'documentdateon'},
            {type: 'int', name: 'fromlocationid'},
            {type: 'int', name: 'tolocationid'},
            {type: 'int', name: 'cancelby'},
            {type: 'date', name: 'cancelon'},
            
            // view
            {type: 'string', name: 'partnername'},
            {type: 'string', name: 'fromlocationname'},
            {type: 'string', name: 'tolocationname'},
            {type: 'string', name: 'cancelbyname'},

            // common table
            {type: 'date', name: 'createdon'},
            {type: 'int', name: 'createdby'},
            {type: 'date', name: 'modifiedon'},
            {type: 'int', name: 'modifiedby'},
            {type: 'int', name: 'status'},

            // common view
            {type: 'string', name: 'cancelbyname'},
            {type: 'string', name: 'createdbyname'},
            {type: 'string', name: 'modifiedbyname'},
    ]
});
