Ext.define('snap.model.RegisterRemarks', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'mykadno'},
        {type: 'string', name: 'remarks'},
        {type: 'int', name: 'status'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'createdon'},        
        {type: 'int', name: 'modifiedby'},
        {type: 'date', name: 'modifiedon'},
    ]
});
