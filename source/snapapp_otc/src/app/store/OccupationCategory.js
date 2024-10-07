Ext.define('snap.store.OccupationCategory', {
    extend: 'Ext.data.Store',

    alias: 'store.OccupationCategory',   
    model: 'snap.model.OccupationCategory',

    storeId:'occupationcategory', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myaccountholder&action=getOccupationCategory',		
        reader: {
            type: 'json',
            rootProperty: 'occupationcategory',
            idProperty: 'occupation_category'            
        },	
    },
   
});
