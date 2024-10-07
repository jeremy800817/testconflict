Ext.define('snap.store.ProductItems', {
    extend: 'Ext.data.Store',
    alias: 'store.ProductItems',   
    model: 'snap.model.ProductItems',  
    storeId:'productitemsstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=product&action=getProducts',		
        reader: {
            type: 'json',
            rootProperty: 'items',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
