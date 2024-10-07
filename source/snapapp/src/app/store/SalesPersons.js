Ext.define('snap.store.SalesPersons', {
    extend: 'Ext.data.Store',
    alias: 'store.SalesPersons',   
    model: 'snap.model.SalesPersons',  
    storeId:'salespersonsstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=user&action=getSalesPersons',		
        reader: {
            type: 'json',
            rootProperty: 'salesmen',
            idProperty: 'product_list'            
        },	
    },
    autoLoad: true,
});
