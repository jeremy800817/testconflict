Ext.define('snap.store.BankAccounts', {
    extend: 'Ext.data.Store',

    alias: 'store.BankAccounts',   
    model: 'snap.model.BankAccounts',

    storeId:'bankaccounts', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=myaccountholder&action=getBankAccounts',		
        reader: {
            type: 'json',
            rootProperty: 'bankaccounts',
            idProperty: 'bank_accounts'            
        },	
    },
   
});
