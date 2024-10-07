
Ext.define('snap.store.SharedBranchStore', {
    extend: 'Ext.data.Store',
    alias: 'store.SharedBranchStore',   
    model: 'snap.model.SharedBranchStore',  
    storeId:'sharedbranchstore', 
    // proxy: {
    //     type: 'ajax',	       	
    //     url: 'index.php?hdl=collection&action=getSharedBranchList',		
    //     reader: {
    //         type: 'json',
    //         rootProperty: 'results',
    //         idProperty: 'branch_list'            
    //     },	
    // },
    //autoLoad: true,
});