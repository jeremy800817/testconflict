Ext.define('snap.store.OtcAccountHolder', {
    extend: 'Ext.data.Store',

    alias: 'store.OtcAccountHolder',   
    model: 'snap.model.OtcAccountHolder',

    storeId:'otcaccountholder', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myaccountholder&action=getOtcAccountHolders',		
        reader: {
            type: 'json',
            rootProperty: 'otcaccountholder',
            idProperty: 'otc_account_holder'            
        },	
    },
   
});
