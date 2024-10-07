//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.MintedGoldbarInventory', {
    extend: 'snap.store.Base',    
    model: 'snap.model.MintedGoldbarInventory',
    alias: 'store.MintedGoldbarInventory',
	proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=goldbarstatus&action=getMintedGoldbarDetails',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
