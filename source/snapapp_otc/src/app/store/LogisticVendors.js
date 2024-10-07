Ext.define('snap.store.LogisticVendors', {
    extend: 'Ext.data.Store',
    alias: 'store.LogisticVendors',   
    model: 'snap.model.LogisticVendors',  
    storeId:'logisticvendorsstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getLogisticsVendors',		
        reader: {
            type: 'json',
            rootProperty: 'vendors',
            idProperty: 'vendors_list'            
        },	
    },
    //autoLoad: true,
});
