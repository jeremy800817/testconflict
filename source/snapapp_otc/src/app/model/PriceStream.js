Ext.define('snap.model.PriceStream', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'providerid'},
			{type: 'string', name: 'providerpriceid'},
			{type: 'string', name: 'uuid'},
            {type: 'int', name: 'currencyid'},
            {type: 'float', name: 'companybuyppg'},
            {type: 'float', name: 'companysellppg'},
            {type: 'float', name: 'rawfxusdbuy'},
            {type: 'float', name: 'rawfxusdsell'},
            {type: 'string', name: 'rawfxsource'},
            {type: 'int', name: 'pricesourceid'},
            {type: 'date', name: 'pricesourceon'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},

            {type: 'string', name: 'pricesourcename'},
            {type: 'string', name: 'providername'},
            {type: 'string', name: 'createdbyname'},
            {type: 'string', name: 'modifiedbyname'},

    ]
});
