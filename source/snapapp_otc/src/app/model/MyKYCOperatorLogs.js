Ext.define('snap.model.MyKYCOperatorLogs', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'type'},
        {type: 'int', name: 'accountholderid'},
        {type: 'string', name: 'remarks'},
        {type: 'int', name: 'approvedby'},
        {type: 'date', name: 'approvedon'},
        {type: 'int', name: 'status'},

        {type: 'date', name: 'createdon'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'createdby'},
        {type: 'int', name: 'modifiedby'},
    ]
});
