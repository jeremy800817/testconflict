Ext.define('snap.store.TradingScheduleTag', {
    extend: 'Ext.data.Store',
    alias: 'store.TradingScheduleTag',   
    model: 'snap.model.TradingScheduleTag',  
    storeId:'tradingscheduletagstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getTradingScheduleTags',		
        reader: {
            type: 'json',
            rootProperty: 'tradingschedule',        
			idProperty: 'tradingschedule'      			
        },	
    },
    //autoLoad: true,
});
