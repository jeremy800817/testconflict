Ext.define('snap.store.TraderOrdersKTP', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersKTP',
    alias: 'store.TraderOrdersKTP',
	storeId:'traderordersktp', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=1',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersPkbgoldSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersPkbgoldSummary',
    alias: 'store.TraderOrdersPkbgoldSummary',
	storeId:'traderorderspkbgoldsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=pkbgoldsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});

Ext.define('snap.store.TraderOrdersBumiragoldSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersBumiragoldSummary',
    alias: 'store.TraderOrdersBumiragoldSummary',
	storeId:'traderordersbumiragoldsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=bumiragoldsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
// Ext.define('snap.store.TraderOrders1', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrders1',
//     alias: 'store.TraderOrders1',
// 	storeId:'traderorders1', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=1.1',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrders2', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrders2',
//     alias: 'store.TraderOrders2',
// 	storeId:'traderorders2', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=2',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrders3', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrders3',
//     alias: 'store.TraderOrders3',
// 	storeId:'traderorders3', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=3',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrders4', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrders4',
//     alias: 'store.TraderOrders4',
// 	storeId:'traderorders4', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=4',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrders5', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrders5',
//     alias: 'store.TraderOrders5',
// 	storeId:'traderorders5', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=5',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrdersPOS01', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrdersPOS01',
//     alias: 'store.TraderOrdersPOS01',
// 	storeId:'traderorderspos01', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=pos01',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrdersPOS02', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrdersPOS02',
//     alias: 'store.TraderOrdersPOS02',
// 	storeId:'traderorderspos02', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=pos02',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderOrdersMib', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrdersMib',
//     alias: 'store.TraderOrdersMib',
// 	storeId:'traderordersmib', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=mib',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderFutureOrdersMib', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderFutureOrdersMib',
//     alias: 'store.TraderFutureOrdersMib',
// 	storeId:'traderfutureordersmib', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=mibfuture',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });
// Ext.define('snap.store.TraderFutureOrdersMibSummary', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderFutureOrdersMibSummary',
//     alias: 'store.TraderFutureOrdersMibSummary',
// 	storeId:'traderfutureordersmibsummary', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=mibfuturesummary',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });

// Ext.define('snap.store.TraderOrdersGogoldSummary', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.TraderOrdersGogoldSummary',
//     alias: 'store.TraderOrdersGogoldSummary',
// 	storeId:'traderordersgogoldsummary', 
//     proxy: {
//         type: 'ajax',	       	
//         url: 'index.php?hdl=trader&action=getOrders&grid=gogoldsummary',		
//         reader: {
//             type: 'json',
//             rootProperty: 'records',  
//         }            
//     },
//     autoLoad: true
// });

