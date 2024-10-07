Ext.define('snap.store.MyVaultItem', {
    extend: 'snap.store.VaultItem',
    model: 'snap.model.MyVaultItem',
    alias: 'store.MyVaultItem',
    autoLoad: true,
    /*listeners : {
        beforeload: function(store, operation, options){
            debugger;
          
        }
    }*/
});
