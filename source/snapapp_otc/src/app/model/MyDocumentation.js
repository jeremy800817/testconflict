Ext.define('snap.model.MyDocumentation', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'locales'},
        {type: 'string', name: 'name'},
        {type: 'string', name: 'code'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'string', name: 'modifiedbyname'},
        {type: 'string', name: 'modifiedbyname'},
    ]
});
            
       