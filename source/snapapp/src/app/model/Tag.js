//
Ext.define('snap.model.Tag', {
    extend: 'snap.model.Base',
    fields: [
        {   type: 'int', name: 'id' },
        {   type: 'string', name: 'category'},
        {   type: 'string', name: 'code' },
        {   type: 'string', name: 'description'},
        {   type: 'string', name: 'value'},
        {   type: 'int', name: 'modifiedby'},
        {   type: 'int', name: 'createdby'},
		{   type: 'date', name: 'modifiedon'},
        {   type: 'date', name: 'createdon'},
        {   type: 'int', name: 'status', defaultValue: 1}
    ]
});
