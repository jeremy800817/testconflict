Ext.define('snap.model.UserActivityLog', {
    extend: 'snap.model.Base',
    fields: [

        {type: 'int', name: 'id'},
        {type: 'int', name: 'usrid'},
        {type: 'string', name: 'username'},
        {type: 'string', name: 'module'},
        {type: 'string', name: 'action'},
        {type: 'string', name: 'activitydetail'},
        {type: 'string', name: 'ip'},
        {type: 'string', name: 'browser'},
        {type: 'string', name: 'activitytime'},
        {type: 'string', name: 'name'},
        {type: 'string', name: 'email'},
        {type: 'string', name: 'type'},
        {type: 'string', name: 'status'},
        {type: 'string', name: 'lastlogin'},
        {type: 'string', name: 'lastloginip'},
    ]
});
