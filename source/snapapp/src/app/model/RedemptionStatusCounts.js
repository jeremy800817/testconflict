Ext.define('snap.model.RedemptionStatusCounts', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name:'pendingrequestscount'},
        { type: 'int', name:'confirmedrequestscount'},
        { type: 'int', name:'pendingdeliveryrequestscount'},  
		{ type: 'int', name:'completedrequestscount'},  		
    ]
});
            
       