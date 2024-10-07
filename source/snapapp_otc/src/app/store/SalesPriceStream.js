// Ext.Loader.setConfig({
// 	enabled: true,
// 	// paths: {
// 	// 	'Ext.ux.data.proxy.WebSocket': './bower_components/ext.ux.data.proxy.websocket/WebSocket.js',
// 	// 	'Ext.ux.WebSocket': './bower_components/ext.ux.websocket/WebSocket.js'
// 	// }
// });
// Ext.Loader.setConfig({
// 	enabled: true
// });
Ext.require(['Ext.ux.WebSocketProxy']);
var prevbuyprice = prevsellprice = 0;
// var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9';
// var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&code=INTLX.GTP_T1';
// var websocketurl = 'wss://gtp2uat.ace2u.com/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&air=1&code=INTLX.GTP_T1';
Ext.define('snap.store.SalesPriceStream', {
    extend: 'Ext.data.Store',
    
    // plugins: ['Ext.ux.WebSocket'],
    // plugins: ['Ext.ux.data.proxy.WebSocket','Ext.ux.WebSocket'],

    // plugins: ['Ext.ux.WebSocket'],
    // require: ['Ext.ux.data.proxy.WebSocket'],

	alias: 'store.SalesPriceStream',
	model: 'snap.model.SalesPriceStream',
	storeId: 'salesPriceStream',
	
	autoLoad: true,
});
