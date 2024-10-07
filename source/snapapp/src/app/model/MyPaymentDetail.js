Ext.define('snap.model.MyPaymentDetail', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'float', name: 'amount'},
        {type: 'string', name: 'paymentrefno'},
        {type: 'string', name: 'gatewayrefno'},
        {type: 'string', name: 'sourcerefno'},
        {type: 'string', name: 'signeddata'},
        {type: 'string', name: 'location'},
        {type: 'float', name: 'gatewayfee'},
        {type: 'float', name: 'customerfee'},
        {type: 'string', name: 'token'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'transactiondate'},
        {type: 'date', name: 'requestedon'},
        {type: 'date', name: 'successon'},
        {type: 'date', name: 'failedon'},
        {type: 'date', name: 'refundedon'},
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
            
       