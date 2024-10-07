Ext.define('snap.store.ProviderGroup', {
    extend: 'Ext.data.Store',
    alias: 'store.ProviderGroup',   
    model: 'snap.model.ProviderGroup',  
    storeId:'providergroup', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getProviderGroupTags',		
        reader: {
            type: 'json',
            rootProperty: 'providergroup',
            idProperty: 'providergroup'            
        },	
    },
    //autoLoad: true,
});
