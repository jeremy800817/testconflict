Ext.define('snap.model.MyMonthlyStorageFee', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'float', name: 'xau'},
        {type: 'float', name: 'price'},
        {type: 'float', name: 'amount'},
        {type: 'float', name: 'adminfeexau'},
        {type: 'float', name: 'storagefeexau'},
        {type: 'float', name: 'ledcurrentxau'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achmykadno'},
        {type: 'string', name: 'partnername'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'chargedon'},
        {type: 'date', name: 'createdon'},
        {type: 'date', name: 'modifiedon'},
    ]
});   
       