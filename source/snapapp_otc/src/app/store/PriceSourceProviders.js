Ext.define('snap.store.PriceSourceProviders', {
    extend: 'Ext.data.Store',
    alias: 'store.PriceSourceProviders',   
    model: 'snap.model.PriceSourceProviders',  
    storeId:'pricesourceproviders', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getPriceSourceTags',		
        reader: {
            type: 'json',
            rootProperty: 'pricesources',
            idProperty: 'pricesources'            
        },	
    },
    //autoLoad: true,
});
