Ext.define('snap.model.MyAdminFee', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'float', name: 'xau'},
        {type: 'float', name: 'price'},
        {type: 'float', name: 'amount'},
        {type: 'float', name: 'adminfeexau'},
        {type: 'float', name: 'storagefeexau'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achmykadno'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'chargedon'},
        {type: 'date', name: 'createdon'},
        {type: 'date', name: 'modifiedon'},
    ]
});   
       