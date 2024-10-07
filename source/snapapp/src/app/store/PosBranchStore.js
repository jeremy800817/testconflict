
Ext.define('snap.store.PosBranchStore', {
    extend: 'Ext.data.Store',
    alias: 'store.PosBranchStore',   
    model: 'snap.model.PosBranchStore',  
    storeId:'posbranchstore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=collection&action=getPosBranchList',		
        reader: {
            type: 'json',
            rootProperty: 'results',
            idProperty: 'branch_list'            
        },	
    },
    //autoLoad: true,
});