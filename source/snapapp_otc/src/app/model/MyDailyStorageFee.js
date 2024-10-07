Ext.define('snap.model.MyDailyStorageFee', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'float', name: 'xau'},
        {type: 'float', name: 'adminfeexau'},
        {type: 'float', name: 'storagefeexau'},
        {type: 'float', name: 'balancexau'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achmykadno'},
        {type: 'date', name: 'calculatedon' },
        {type: 'int', name: 'status'},
        {type: 'date', name: 'createdon'},
        {type: 'date', name: 'modifiedon'},
        {type: 'string', name: 'partnername'},
    ]
});
            
       