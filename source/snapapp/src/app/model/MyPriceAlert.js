Ext.define('snap.model.MyPriceAlert', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'string', name: 'type' },
        { type: 'float', name: 'amount' },
        { type: 'string', name: 'remarks' },
        { type: 'int', name: 'accountholderid' },
        { type: 'int', name: 'priceproviderid' },
        { type: 'int', name: 'status' },
        { type: 'date', name: 'lasttriggeredon' },
        { type: 'int',  name:  'triggered' },
        { type: 'date', name: 'senton' },
        { type: 'date', name: 'createdon' },
        { type: 'date', name: 'modifiedon' },
        { type: 'string', name: 'priceprovidercode' },
        { type: 'string', name: 'priceprovidername' },
        { type: 'string', name: 'accountholdermykadno' },
        { type: 'string', name: 'accountholdercode' },
        { type: 'string', name: 'accountholderfullname' },
    ]
});
