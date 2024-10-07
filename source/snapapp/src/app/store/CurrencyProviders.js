Ext.define('snap.store.CurrencyProviders', {
    extend: 'Ext.data.Store',
    alias: 'store.CurrencyProviders',   
    model: 'snap.model.CurrencyProviders',  
    storeId:'currencyproviders', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getCurrencyTags',		
        reader: {
            type: 'json',
            rootProperty: 'currency',
            idProperty: 'currency'            
        },	
    },
    //autoLoad: true,
});
