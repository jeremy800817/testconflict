Ext.define('snap.model.MyKYCResult', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'provider'},
        {type: 'string', name: 'remarks'},
        {type: 'string', name: 'data'},
        {type: 'string', name: 'result'},
        {type: 'int', name: 'submissionid'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
    ]
});
            
       