Ext.define('snap.model.PartnerBranchMap', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'string', name: 'branchcode'},
			{type: 'string', name: 'name'},
            {type: 'string', name: 'partnercode'},
			{type: 'string', name: 'sapcode'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},

    ]
});
