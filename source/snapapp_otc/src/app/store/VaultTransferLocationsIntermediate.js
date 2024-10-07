Ext.define('snap.store.VaultTransferLocationsIntermediate', {
    extend: 'Ext.data.Store',
    alias: 'store.VaultTransferLocationsIntermediate',   
    model: 'snap.model.VaultTransferLocationsIntermediate',  
    storeId:'VaultTransferLocationsIntermediatestore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=vaultitem&action=getTransferLocationsIntermediate',		
        reader: {
            type: 'json',
            rootProperty: 'locations',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
