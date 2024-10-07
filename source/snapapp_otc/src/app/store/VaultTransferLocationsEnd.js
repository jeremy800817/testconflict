Ext.define('snap.store.VaultTransferLocationsEnd', {
    extend: 'Ext.data.Store',
    alias: 'store.VaultTransferLocationsEnd',   
    model: 'snap.model.VaultTransferLocationsEnd',  
    storeId:'VaultTransferLocationsEndstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=vaultitem&action=getTransferLocationsEnd',		
        reader: {
            type: 'json',
            rootProperty: 'locations',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
