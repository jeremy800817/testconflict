Ext.define('snap.model.MyAmlaScanLog', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'string', name: 'status' },
        { type: 'date', name: 'scannedon' },
        { type: 'string', name: 'scmremarks' },
        { type: 'string', name: 'scmmatcheddata' },
        { type: 'date', name: 'scmmatchedon' },
        { type: 'string', name: 'scmstatus' },
        { type: 'string', name: 'sclsourcetype' },
        // {type: 'date', name: 'createdon'},
        // {type: 'int', name: 'createdby'},
        // {type: 'date', name: 'modifiedon'},
        // {type: 'int', name: 'modifiedby'},
    ]
});

