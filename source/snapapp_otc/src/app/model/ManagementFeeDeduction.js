Ext.define('snap.model.ManagementFeeDeduction', {
    extend: 'snap.model.Base',
    fields: [

        {type: 'int', name: 'id'},
        {type: 'int', name: 'accountholderid'},
        {type: 'string', name: 'refno'},
        {type: 'string', name: 'code'},
        {type: 'string', name: 'desc'},
        {type: 'string', name: 'deducttype'},
        {type: 'int', name: 'status'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'string', name: 'achpartnercusid'},
        {type: 'string', name: 'achfullname'},
        {type: 'string', name: 'achaccountnumber'},
        {type: 'string', name: 'achaccountholdercode'},
        {type: 'string', name: 'achbranch'},
        {type: 'string', name: 'achtype'},
        {type: 'string', name: 'achno'},
        {type: 'float', name: 'pdtamount'},
        {type: 'string', name: 'pdtsourcerefno'},
    ]
});
       