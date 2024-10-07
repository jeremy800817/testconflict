Ext.define('snap.store.OccupationSubCategory', {
    extend: 'Ext.data.Store',

    alias: 'store.OccupationSubCategory',   
    model: 'snap.model.OccupationSubCategory',

    storeId:'occupationsubcategory', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myaccountholder&action=getOccupationSubCategory',		
        reader: {
            type: 'json',
            rootProperty: 'occupationsubcategory',
            idProperty: 'occupation_sub_category'            
        },	
    },
   
});
