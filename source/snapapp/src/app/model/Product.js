Ext.define('snap.model.Product', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'int', name: 'categoryid'},
        {type: 'string', name: 'code'},
        {type: 'string', name: 'name'},
        {type: 'int', name: 'companycansell'},
        {type: 'int', name: 'companycanbuy'},
        {type: 'int', name: 'trxbyweight'},
        {type: 'int', name: 'trxbycurrency'},
        {type: 'int', name: 'deliverable'},
        {type: 'string', name: 'sapitemcode'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'int', name: 'status'},

    ]
});
