Ext.define('snap.store.RedemptionStatusCounts', {
    extend: 'snap.store.Base',
    model: 'snap.model.Redemption',
    alias: 'store.RedemptionStatusCounts',
    autoLoad: true,	
	listeners: {
    render: function(store) {
        store.on('load', function(records) {
			console.log(records);
            //var count = records.length; //or store.getTotalCount(), if that's what you want
            //grid.down('#numRecords').setText('Number of Records: ' + count);
        });    
    }
}
});
