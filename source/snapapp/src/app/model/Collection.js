Ext.define('snap.model.Collection', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'int', name: 'partnerid' },
        { type: 'int', name: 'salespersonid' },
        { type: 'int', name: 'comments'},
        { type: 'string', name: 'totalxauexpected' },
        { type: 'string', name: 'totalgrossweight' },
        { type: 'string', name: 'totalxaucollected' },
        { type: 'string', name: 'vatsum' },
        { type: 'string', name: 'createdon' },
        { type: 'string', name: 'createdby' },
        { type: 'string', name: 'modifiedon' },
        { type: 'string', name: 'modifiedby' },        
        { type: 'int', name: 'status' },		
    ]
});
