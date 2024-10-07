Ext.define('snap.model.PriceValidation', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'int', name: 'pricestreamid'},
			{type: 'string', name: 'apiversion'},
			{type: 'string', name: 'uuid'},
			{type: 'string', name: 'requestedtype'},
			{type: 'float', name: 'premiumfee'},
			{type: 'float', name: 'refineryfee'},
            {type: 'float', name: 'price'},
			{type: 'string', name: 'validtill'},
			{type: 'int', name: 'orderid'},
			{type: 'string', name: 'reference'},
			{type: 'date', name: 'timestamp'},
			{type: 'date', name: 'createdon'},
			{type: 'string', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'string', name: 'modifiedby'},
			{type: 'string', name: 'status'},
			{type: 'string', name: 'partnername'},
			{type: 'string', name: 'partnercode'},
			{type: 'string', name: 'createdbyname'},
			{type: 'string', name: 'modifiedbyname'}
    ]
});
