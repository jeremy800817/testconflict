Ext.define('snap.store.UnfulfilledPO', {
    extend: 'snap.store.Base',
    model: 'snap.model.UnfulfilledPO',
    alias: 'store.UnfulfilledPO',
	storeId:'unfulfilledpo', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=order&action=getUnfulfilledStatements',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
