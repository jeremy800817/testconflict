Ext.define('snap.model.PosCollection', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'int', name: 'salespersonid'},
			{type: 'string', name: 'comments'},
            {type: 'string', name: 'jsonpostpayload'},
			{type: 'float', name: 'totalxauexpected'},
			{type: 'float', name: 'totalgrossweight'},
			{type: 'float', name: 'totalxaucollected'},
			{type: 'float', name: 'vatsum'},

			{type: 'string', name: 'partnername'},
            {type: 'string', name: 'partnercode'},
			{type: 'string', name: 'salespersonname'},
            {type: 'string', name: 'salespersonemail'},
			{type: 'string', name: 'createdbyname'},
            {type: 'string', name: 'modifiedbyname'},
			
    ]
});
