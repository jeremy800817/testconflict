var prevbuyprice = prevsellprice = 0;
var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9'
Ext.require(['Ext.ux.WebSocketProxy']);
Ext.define('snap.store.PriceStreaming', {
	// plugins: ['Ext.ux.WebSocket'],
	extend: 'Ext.data.Store',
	alias: 'store.PriceStreaming',
	model: 'snap.model.PriceStreaming',
	storeId: 'priceStreaming',
	proxy: {
		type: 'websocket',
		storeId: 'priceStreaming',
		url: websocketurl,	
		// url: 'ws://bo.gtp.development:80/nchan/sub',
		reader: {
			type: 'json',
			rootProperty: 'data',
			transform: {
				fn: function (data) {
					var companybuyppg = data[0].companybuy;
					var copanysellppg = data[0].companysell;
					var bgcolorbuyprice = bgcolorsellprice = '#669999';
					if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)) { bgcolorbuyprice = 'red' }
					else if (parseFloat(companybuyppg) > parseFloat(prevbuyprice)) { bgcolorbuyprice = 'green' }
					else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)) { bgcolorbuyprice = '#669999' };

					if (parseFloat(copanysellppg) < parseFloat(prevsellprice)) { bgcolorsellprice = 'red' }
					else if (parseFloat(copanysellppg) > parseFloat(prevsellprice)) { bgcolorsellprice = 'green' }
					else if (parseFloat(copanysellppg) == parseFloat(prevsellprice)) { bgcolorsellprice = '#669999' };

					Ext.get('buybtn').setStyle('background-color', bgcolorbuyprice);
					Ext.get('sellbtn').setStyle('background-color', bgcolorsellprice);

					Ext.get('spotorder_buy_price').dom.innerHTML = 'RM ' + companybuyppg;
					Ext.get('spotorder_sell_price').dom.innerHTML = 'RM ' + copanysellppg;

					Ext.getCmp('sellprice').setValue(copanysellppg);
					Ext.getCmp('buyprice').setValue(companybuyppg);
					Ext.getCmp('uuid').setValue(data[0].uuid);

					prevbuyprice = companybuyppg;
					prevsellprice = copanysellppg;

					return data[0];
				},
				//scope: this
			},
		},	
	},
	autoLoad: true,
});
