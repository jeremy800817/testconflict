Ext.define('snap.store.ProductCategories', {
    extend: 'Ext.data.Store',
    alias: 'store.ProductCategories',   
    model: 'snap.model.ProductCategories',  
    storeId:'productcategories', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=tag&action=getProductCategoryTags',		
        reader: {
            type: 'json',
            rootProperty: 'productcategories',
            idProperty: 'product_categories'            
        },	
    },
    autoLoad: true,
});
