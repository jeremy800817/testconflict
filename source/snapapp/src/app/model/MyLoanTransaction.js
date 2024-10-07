Ext.define('snap.model.MyLoanTransaction', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'int', name: 'achid'},
        {type: 'string', name: 'transactiontype'},
        {type: 'string', name: 'gtrrefno'},
        {type: 'float', name: 'transactionamount'},
        {type: 'float', name: 'xau'},

        {type: 'date', name: 'createdon'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'createdby'},
        {type: 'int', name: 'modifiedby'},
    ]
});
        