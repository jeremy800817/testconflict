Ext.define('snap.model.SalesCommission', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'salespersonid'},
            {type: 'date', name: 'startdate'},
            {type: 'date', name: 'enddate'},
			{type: 'float', name: 'totalcompanybuy'},
			{type: 'float', name: 'totalcompanysell'},
            {type: 'float', name: 'totalxau'},
			{type: 'float', name: 'totalfee'},
			{type: 'date', name: 'createdon'},
			{type: 'int', name: 'createdby'},
			{type: 'date', name: 'modifiedon'},
			{type: 'int', name: 'modifiedby'},
			{type: 'int', name: 'status'},

    ]
});
