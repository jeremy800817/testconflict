Ext.define('snap.model.PriceProvider', {
    extend: 'snap.model.Base',
    fields: [
        {type: 'int', name: 'id'},
        {type: 'int', name: 'index'},
        {type: 'string', name: 'code'},
        {type: 'string', name: 'name'},
        {type: 'int', name: 'pricesourceid'},
        {type: 'int', name: 'productcategoryid'},
        {type: 'int', name: 'pullmode'},
        {type: 'int', name: 'currencyid'},
        {type: 'string', name: 'whitelistip'},
        {type: 'string', name: 'url'},
        {type: 'string', name: 'connectinfo'},
        {type: 'int', name: 'lapsetimeallowance'},

        {type: 'string', name: 'futureorderstrategy'},
        {type: 'string', name: 'futureorderparams'},

        {type: 'int', name: 'providergroupid'},

        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'int', name: 'status'},

        
        {type: 'string', name: 'productcategoryname'},
        {type: 'string', name: 'pricesourcecode'},
        {type: 'string', name: 'currencycode'},
        {type: 'string', name: 'createdbyname'},
        {type: 'string', name: 'modifiedbyname'},

    ]
});
