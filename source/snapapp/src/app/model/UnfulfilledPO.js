Ext.define('snap.model.UnfulfilledPO', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'docNum'},
        	{type: 'string', name: 'docDate'},
        	{type: 'string', name: 'cardCode'},
        	{type: 'string', name: 'docEntry'},
        	{type: 'int', name: 'lineNum'},
        	{type: 'string', name: 'itemCode'},
        	{type: 'string', name: 'dscription'},
        	{type: 'float', name: 'quantity'},
        	{type: 'float', name: 'openQty'},
        	{type: 'float', name: 'draftQty'},
        	{type: 'float', name: 'opndraft'},
        	{type: 'string', name: 'price'},
        	{type: 'string', name: 'docTotal'},
        	{type: 'float', name: 'vatSum'},
        	{type: 'string', name: 'docTotalAmt'},
        	{type: 'string', name: 'draftGRN'},
        	{type: 'string', name: 'u_GTPREFNO'},
        	{type: 'string', name: 'comments'},
    ]
});
