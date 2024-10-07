Ext.define('snap.store.TraderOrders', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders',
    alias: 'store.TraderOrders',
	storeId:'traderorders', 
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

Ext.define('snap.store.TraderOrdersChainedStore', {
    extend: 'Ext.data.ChainedStore',
    alias: 'store.TraderOrdersChainedStore',
    storeId: 'TraderOrdersChainedStore',
    //source using storeID
    source: 'traderorders'

});

Ext.define('snap.store.TraderOrders1', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders1',
    alias: 'store.TraderOrders1',
	storeId:'traderorders1', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=1.1',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});

Ext.define('snap.store.TraderOrders1ChainedStore', {
    extend: 'Ext.data.ChainedStore',
    alias: 'store.TraderOrders1ChainedStore',
    storeId: 'TraderOrders1ChainedStore',
    //source using storeID
    source: 'traderorders1'

});

Ext.define('snap.store.TraderOrders1_2', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders1_2',
    alias: 'store.TraderOrders1_2',
	storeId:'traderorders1_2', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=1.2',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});

Ext.define('snap.store.TraderOrders2', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders2',
    alias: 'store.TraderOrders2',
	storeId:'traderorders2', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=2',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrders3', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders3',
    alias: 'store.TraderOrders3',
	storeId:'traderorders3', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=3',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrders4', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders4',
    alias: 'store.TraderOrders4',
	storeId:'traderorders4', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=4',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrders5', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrders5',
    alias: 'store.TraderOrders5',
	storeId:'traderorders5', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=5',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersPOS01', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersPOS01',
    alias: 'store.TraderOrdersPOS01',
	storeId:'traderorderspos01', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=pos01',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
//Buyback Total Pos
Ext.define('snap.store.TraderOrdersBuybackTotalPos', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersPOS01',
    alias: 'store.TraderOrdersBuybackTotalPos',
	storeId:'traderordersbuybacktotalpos', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=pos01',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
//Buyback Total Miga
Ext.define('snap.store.TraderOrdersBuybackTotalMiga', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersPOS01',
    alias: 'store.TraderOrdersBuybackTotalMiga',
	storeId:'traderordersbuybacktotalmiga', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=pos01',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
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
Ext.define('snap.store.TraderOrdersMib', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersMib',
    alias: 'store.TraderOrdersMib',
	storeId:'traderordersmib', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=mib',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderFutureOrdersMib', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderFutureOrdersMib',
    alias: 'store.TraderFutureOrdersMib',
	storeId:'traderfutureordersmib', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=mibfuture',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderFutureOrdersMibSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderFutureOrdersMibSummary',
    alias: 'store.TraderFutureOrdersMibSummary',
	storeId:'traderfutureordersmibsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=mibfuturesummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});

Ext.define('snap.store.TraderOrdersGogoldSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersGogoldSummary',
    alias: 'store.TraderOrdersGogoldSummary',
	storeId:'traderordersgogoldsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=gogoldsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});

Ext.define('snap.store.TraderOrdersOnecallSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersOnecallSummary',
    alias: 'store.TraderOrdersOnecallSummary',
	storeId:'traderordersonecallsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=onecallsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersOnecentSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersOnecentSummary',
    alias: 'store.TraderOrdersOnecentSummary',
	storeId:'traderordersonecentsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=onecentsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersMcashSummary', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersMcashSummary',
    alias: 'store.TraderOrdersMcashSummary',
	storeId:'traderordersmcashsummary', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=mcashsummary',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersOrderTotals', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersOrderTotals',
    alias: 'store.TraderOrdersOrderTotals',
	storeId:'traderordersordertotals', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=traderordersordertotals',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersOrderTotals2', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersOrderTotals',
    alias: 'store.TraderOrdersOrderTotals2',
	storeId:'traderordersordertotals2', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=traderordersordertotals',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersBuySell', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersBuySell',
    alias: 'store.TraderOrdersBuySell',
	storeId:'traderordersbuysell', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=traderordersbuysell',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
Ext.define('snap.store.TraderOrdersCustomerQueueBuySell', {
    extend: 'snap.store.Base',
    model: 'snap.model.TraderOrdersCustomerQueueBuySell',
    alias: 'store.TraderOrdersCustomerQueueBuySell',
	storeId:'traderorderscustomerqueuebuysell', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=trader&action=getOrders&grid=traderorderscustomerqueuebuysell',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    autoLoad: true
});
