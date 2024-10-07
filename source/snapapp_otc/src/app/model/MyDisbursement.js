Ext.define('snap.model.MyDisbursement', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'float', name: 'amount'},
        {type: 'int', name: 'bankid'},
        {type: 'string', name: 'accountname'},
        {type: 'string', name: 'accountnumber'},
        {type: 'int', name: 'acebankcode'},
        {type: 'float', name: 'fee'},
        {type: 'string', name: 'refno'},
        {type: 'int', name: 'accountholderid'},
        {type: 'int', name: 'status'},
        {type: 'string', name: 'gatewayrefno'},
        {type: 'string', name: 'transactionrefno'},
        {type: 'date', name: 'requestedon'},
        {type: 'date', name: 'disbursedon'},
        {type: 'float', name: 'verifiedamount'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},

        {type: 'string', name: 'accountholdername'},
        {type: 'string', name: 'accountholdercode'},
        {type: 'string', name: 'createdbyname'},
        {type: 'string', name: 'modifiedbyname'},
    ]
});
            
       