Ext.define('snap.model.MyAccountClosure', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'string', name: 'achmykadno' },
        { type: 'string', name: 'achaccountholdercode' },
        { type: 'string', name: 'achfullname' },
        { type: 'string', name: 'remarks' },
        { type: 'string', name: 'locreason' },
        { type: 'string', name: 'statustext' },
        { type: 'date', name: 'requestedon' },
        { type: 'date', name: 'closedon' },
        { type: 'date', name: 'createdon' },
        { type: 'date', name: 'modifiedon' },
    ]
});
