Ext.define('snap.model.MyGoldStatement', {
    extend: 'snap.model.Base',
    idProperty: 'index',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achmykadno'},
        {type: 'float', name: 'ordgoldprice'},
        {type: 'float', name: 'xaubalance'},        
        {type: 'float', name: 'amountin'},        
        {type: 'float', name: 'amountout'},        
        {type: 'float', name: 'amountbalance'},        
        {type: 'string', name: 'type'},
        {type: 'string', name: 'debit'},
        {type: 'string', name: 'credit'},
        {type: 'string', name: 'refno'},
        {type: 'string', name: 'remarks'},
        {type: 'string', name: 'saprefno'},
        {type: 'date', name: 'transactiondate'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
    ]
});

