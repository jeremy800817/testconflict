Ext.define('snap.model.MyMonthlySummary', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'string', name: 'accountholdercode'},
        {type: 'string', name: 'fullname'},
        {type: 'string', name: 'partnername'},
        {type: 'string', name: 'mykadno'},
        {type: 'string', name: 'xaubalance'},
        {type: 'string', name: 'amountbalance'},
        {type: 'string', name: 'email'},
        {type: 'string', name: 'phoneno' },
        {type: 'string', name: 'addressline1'},
        {type: 'string', name: 'addressline2'},
        {type: 'string', name: 'addresscity'},
        {type: 'string', name: 'addresspostcode' },
        {type: 'string', name: 'addressstate' },
    ]
});
