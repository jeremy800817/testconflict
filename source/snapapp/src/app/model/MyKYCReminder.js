Ext.define('snap.model.MyKYCReminder', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'int', name: 'accountholderid'},
        {type: 'date', name: 'senton'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
    ]
});
            
       