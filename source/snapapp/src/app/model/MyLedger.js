Ext.define('snap.model.MyLedger', {
    extend: 'snap.model.Base',
    
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'type'},
        {type: 'int', name: 'typeid'},
        {type: 'int', name: 'partnerid'},
        {type: 'int', name: 'accountholderid'},
   
        {type: 'float', name: 'debit'},
        {type: 'float', name: 'credit'},
        {type: 'string', name: 'refno'},
        {type: 'string', name: 'remarks'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'transactiondate'},

        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},

        // view
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achmykadno'},
        {type: 'string', name: 'partnername'},   
        {type: 'string', name: 'partnercode'},
        {type: 'float', name: 'ordgoldprice'},
        {type: 'string', name: 'ordsaprefno'},
        {type: 'float', name: 'amountin'},
        {type: 'float', name: 'amountout'},
        {type: 'float', name: 'amountbalance'},
        {type: 'float', name: 'xaubalance'},
        // {type: 'string', name: 'createdbyname'},
        // {type: 'string', name: 'modifiedbyname'},
    ]
});
