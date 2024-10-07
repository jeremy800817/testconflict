Ext.define('snap.store.VaultTransferLocationsStart', {
    extend: 'Ext.data.Store',
    alias: 'store.VaultTransferLocationsStart',   
    model: 'snap.model.VaultTransferLocationsStart',  
    storeId:'VaultTransferLocationsStartstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart',		
        reader: {
            type: 'json',
            rootProperty: 'locations',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
