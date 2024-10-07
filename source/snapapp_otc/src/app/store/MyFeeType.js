Ext.define('snap.store.MyFeeType', {
    extend: 'Ext.data.Store',
    alias: 'store.MyFeeType',   
    model: 'snap.model.MyFeeType',
    storeId:'MyFeeType', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myfee&action=listmyfeetype',		
        reader: {
            type: 'json',
            rootProperty: 'records',
        },	
    },
    autoLoad: false,
});
