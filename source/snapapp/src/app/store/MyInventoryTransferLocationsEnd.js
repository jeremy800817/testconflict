Ext.define('snap.store.MyInventoryTransferLocationsEnd', {
    extend: 'Ext.data.Store',
    alias: 'store.MyInventoryTransferLocationsEnd',   
    model: 'snap.model.VaultTransferLocationsEnd',  
    storeId:'MyInventoryTransferLocationsEndstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myinventory&action=getTransferLocationsEnd',		
        reader: {
            type: 'json',
            rootProperty: 'locations',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
