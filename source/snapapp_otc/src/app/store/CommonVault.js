
Ext.define('snap.store.CommonVault', {
    extend: 'snap.store.Base',
    model: 'snap.model.CommonVault',
    alias: 'store.CommonVault',
	storeId:'commonvault', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=vaultitem&action=getCommonVaultInfo',		
        reader: {
            type: 'json', 
        }            
    },
    autoLoad: true
});

// fields: ['name', 'data1'],
// data: [{
//     name: 'metric one',
//     grams: 100,
//     usage: 14 // percentage
// }, {
//     name: 'metric two',
//     grams: 100,
//     usage: 16
// }, {
//     name: 'metric three',
//     grams: 100,
//     usage: 14
// }, {
//     name: 'metric four',
//     grams: 100,
//     usage: 6
// }, {
//     name: 'Free',
//     grams: 100,
//     usage: 36
// }]