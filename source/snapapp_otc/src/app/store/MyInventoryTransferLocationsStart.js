Ext.define('snap.store.MyInventoryTransferLocationsStart', {
    extend: 'Ext.data.Store',
    alias: 'store.MyInventoryTransferLocationsStart',   
    model: 'snap.model.VaultTransferLocationsStart',  
    storeId:'MyInventoryTransferLocationsStartstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myinventory&action=getTransferLocationsStart',		
        reader: {
            type: 'json',
            rootProperty: 'locations',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
