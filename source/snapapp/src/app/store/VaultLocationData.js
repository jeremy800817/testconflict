Ext.define('snap.store.VaultLocationData', {
    extend: 'Ext.data.Store',
    alias: 'store.VaultLocationData',   
    model: 'snap.model.VaultLocationData',  
    storeId:'vaultlocationdatastore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=vaultitem&action=getLocation',		
        reader: {
            type: 'json',
            rootProperty: 'location',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
