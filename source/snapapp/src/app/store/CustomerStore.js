// Ext.define('snap.store.CustomerStore', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.CustomerStore',
//     alias: 'store.CustomerStore',   
//     autoLoad: true
// });


Ext.define('snap.store.CustomerStore', {
    extend: 'Ext.data.Store',
    alias: 'store.CustomerStore',   
    model: 'snap.model.CustomerStore',  
    storeId:'customerStore', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=collection&action=getCustomerList',		
        reader: {
            type: 'json',
            rootProperty: 'results',
            idProperty: 'vendors_list'            
        },	
    },
    //autoLoad: true,
});
