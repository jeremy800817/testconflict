Ext.define('snap.model.User', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'username'},
        {type: 'string', name: 'password'},
        {type: 'string', name: 'oldpassword'},
        {type: 'string', name: 'name'},
        {type: 'string', name: 'phoneno'},
        {type: 'string', name: 'email'},
        {type: 'int', name: 'partnerid'},
        {type: 'string', name: 'partnername'},
        {type: 'string', name: 'partnercode'},
        {type: 'string', name: 'type'},
        // {type: 'int', name: 'failtimes'},
        {type: 'date', name: 'expire'},
        {type: 'date', name: 'lastlogin'},
        {type: 'string', name: 'lastloginip'},
        {type: 'string', name: 'resettoken'},
        {type: 'string', name: 'resetrequestedon'},
        {type: 'date', name: 'passwordmodifiedon'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'int', name: 'status'},

    ]
});
