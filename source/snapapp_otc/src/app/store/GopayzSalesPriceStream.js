Ext.require(['Ext.ux.WebSocketProxy']);
var prevbuyprice = prevsellprice = 0;
var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&gopayz=1&code=INTLX.GOGOLD';
// var host_name = window.location.host;
// if (host_name.search('demo') >= 0 || host_name.search('uat') >= 0 ){
//     var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&gopayz=1&code=INTLX.GOPAYZ';
// }else{
//     var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&gopayz=1&code=INTLX.GOPAYZ';
// }
Ext.define('snap.store.GopayzSalesPriceStream', {
    extend: 'Ext.data.Store',
    
    // plugins: ['Ext.ux.WebSocket'],
    // plugins: ['Ext.ux.data.proxy.WebSocket','Ext.ux.WebSocket'],

    // plugins: ['Ext.ux.WebSocket'],
    // require: ['Ext.ux.data.proxy.WebSocket'],

	alias: 'store.GopayzSalesPriceStream',
	model: 'snap.model.SalesPriceStream',
	storeId: 'gopayzsalesPriceStream',
	proxy: {
		type: 'websocket',
        storeId: 'gopayzsalesPriceStream',
        //url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9',	
        url: websocketurl,	
        //url: 'ws://bo.gtp.development:80/nchan/sub',
        //url: 'ws://bo.gtp.development/nchan/sub/1',
		reader: {
			type: 'json',
			rootProperty: 'data',
			transform: {
				fn: function (data) {
                    
					var companybuyppg = data[0].companybuy;
					var companysellppg = data[0].companysell;
                    //var bgcolorbuyprice = bgcolorsellprice = '#669999';
                    
                    /*
					if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)) { bgcolorbuyprice = 'red' }
					else if (parseFloat(companybuyppg) > parseFloat(prevbuyprice)) { bgcolorbuyprice = 'green' }
					else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)) { bgcolorbuyprice = '#669999' };

					if (parseFloat(companysellppg) < parseFloat(prevsellprice)) { bgcolorsellprice = 'red' }
					else if (parseFloat(companysellppg) > parseFloat(prevsellprice)) { bgcolorsellprice = 'green' }
					else if (parseFloat(companysellppg) == parseFloat(prevsellprice)) { bgcolorsellprice = '#669999' };
                    */

					//Ext.get('buybtn').setStyle('background-color', bgcolorbuyprice);
                    //Ext.get('sellbtn').setStyle('background-color', bgcolorsellprice);
                    

                    //Ext.get('spotorder_buy_price').dom.innerHTML = 'RM ' + companybuyppg;
                    // Design for price
                    
                   

                    /*------------------------ Ace Buy Price ------------------------------------*/
                    var acebuy = companybuyppg;
                    // Slice Ace values
                    acebuy = parseFloat(acebuy).toFixed(3);
                    acebuystr = acebuy.toString();
                    acebuytruncatedleft = acebuystr.slice(0, -5);
                    //acebuytruncatedleft = parseFloat(acebuytruncatedleft);

                    // Enlarged values of ace buy/sell (last 4)
                    acebuytruncatedright = acebuystr.substring(acebuy.length-5, acebuy.length);
                    //acebuytruncatedright = parseFloat(acebuytruncatedright);

                    // Set Color Codes
                    // If value > previous
                    if (parseFloat(companybuyppg) > parseFloat(prevbuyprice)){
                        // Green 
                        colortag = '<h1 style="color:#7ED321;display:inline;text-align:center;">';
                    }else if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)){
                        // If value < previous
                        // Red
                        colortag = '<h1 style="color:#C3262E;display:inline;text-align:center;">';
                    }else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)){
                        // If no change
                        colortag = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">';
                    }

                    acebuyprice = colortag + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">'+ acebuytruncatedright +'</p></h1>';
 
                    acebuydesign = '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy (RM)</h2>' +
                    '<br><div>' + 
                    acebuyprice + 
                    '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                    '</div>';
                   // End of Ace Buy 

                   /*------------------------ Ace Sell Price ------------------------------------*/
                   var acesell = companysellppg;
                   // Slice Ace values
                   acesell = parseFloat(acesell).toFixed(3);
                   acesellstr = acesell.toString();
                   aceselltruncatedleft = acesellstr.slice(0, -5);
                   //acebuytruncatedleft = parseFloat(acebuytruncatedleft);

                   // Enlarged values of ace buy/sell (last 4)
                   aceselltruncatedright = acesellstr.substring(acesell.length-5, acesell.length);
                   //acebuytruncatedright = parseFloat(acebuytruncatedright);

                   // Set Color Codes
                   // If value > previous
                   if (parseFloat(companysellppg) > parseFloat(prevsellprice)){
                       // Green 
                       colortag = '<h1 style="color:#7ED321;display:inline;text-align:center;">';
                   }else if (parseFloat(companysellppg) < parseFloat(prevsellprice)){
                       // If value < previous
                       // Red
                       colortag = '<h1 style="color:#C3262E;display:inline;text-align:center;">';
                   }else if (parseFloat(companysellppg) == parseFloat(prevsellprice)){
                       // If no change
                       colortag = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">';
                   }

                   acesellprice = colortag + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">'+ aceselltruncatedright +'</p></h1>';

                   aceselldesign = '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell (RM)</h2>' +
                   '<br><div>' + 
                   acesellprice + 
                   '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                   '</div>';
                  // End of Ace Sell
                    
                  
                 /*------------------------ Ace Buy Price Confirmation------------------------------------*/
                  // Set Color Codes original (0.7em)
                  // If value > previous
                  if (parseFloat(companybuyppg) > parseFloat(prevbuyprice)){
                        // Green 
                        //backgroundcolor = '<div style="padding: 2.7em;background-color:#A7EAAC;">';
                        backgroundcolor = '<div style="padding: 2.7em;background-color:#089000;">';
                        // Get bigger or smaller changes
                        //Ext.getCmp('acebuypricechange').setValue('green');
                    }else if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)){
                        // If value < previous
                        // Red
                        //backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
                        backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
                        // Get bigger or smaller changes
                        //Ext.getCmp('acebuypricechange').setValue('red');
                    }else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)){
                        // If no change
                        //backgroundcolor = '<div style="padding: 0.7em;background-color:#cccccc;">';
                        backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
                        // Get bigger or smaller changes
                        //Ext.getCmp('acebuypricechange').setValue('grey');
                    }

                    // Old Design
                    /*
                    acebuydesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                    '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
                    'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' + 
                    '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                    '</div>';
                    */
                    // New Design
                    acebuydesignconfirmation = backgroundcolor + '<h1 style="color:#ffffff;display:inline;text-align:center;">' + 
                    acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' + 
                    '<h3 style="color:#ffffff;text-align:center;font-style: italic;">per gram</h3>' +
                    '</div>';
                    
                // End of Ace Buy Price Confirmation

                    /*------------------------ Ace Sell Price Confirmation------------------------------------*/
                    // Set Color Codes original (0.7em)
                    // If value > previous
                    if (parseFloat(companysellppg) > parseFloat(prevsellprice)){
                    // Green 
                    //backgroundcolor = '<div style="padding: 2.7em;background-color:#A7EAAC;">';
                    backgroundcolor = '<div style="padding: 2.7em;background-color:#089000;">';
                    // Get changes
                    //Ext.getCmp('acesellpricechange').setValue('green');

                }else if (parseFloat(companysellppg) < parseFloat(prevsellprice)){
                    // If value < previous
                    // Red
                    //backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
                    backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
                    
                    // Get changes
                    //Ext.getCmp('acesellpricechange').setValue('red');
                }else if (parseFloat(companysellppg) == parseFloat(prevsellprice)){
                    // If no changev #cccccc
                    backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
                    // Get changes
                    //Ext.getCmp('acesellpricechange').setValue('grey');
                }

                // Old Design 
                /*
                aceselldesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
                'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' + 
                '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                '</div>';
                */// Old color #404040
                aceselldesignconfirmation = backgroundcolor + '<h1 style="color:#ffffff;display:inline;text-align:center;">' + 
                aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' + 
                '<h3 style="color:#ffffff;text-align:center;font-style: italic;">per gram</h3>' +
                '</div>';
                
                // End of Ace Sell Price Confirmation
               
					Ext.getCmp('gosaleacesellprice').setValue(companysellppg);
					Ext.getCmp('gosaleacebuyprice').setValue(companybuyppg);
                    Ext.getCmp('gosaleorderuuid').setValue(data[0].uuid);
                    
                    prevbuyprice = companybuyppg;
                    prevsellprice = companysellppg;
                    
                    Ext.getCmp('gospotsaleacebuy').setValue(acebuydesign);
                    Ext.getCmp('gospotsaleacesell').setValue(aceselldesign);
                    
                    
                    // For confirmation fields
                    if (Ext.get('salespotorderbuyconfirmationval') != null){
                        Ext.getCmp('salespotorderbuyconfirmationval').setValue(acebuydesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('salespotorderbuyconfirmationxau') != null){
                        Ext.getCmp('salespotorderbuyconfirmationxau').setValue(acebuydesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('salespotordersellconfirmationval') != null){
                        Ext.getCmp('salespotordersellconfirmationval').setValue(aceselldesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('salespotordersellconfirmationxau') != null){
                        Ext.getCmp('salespotordersellconfirmationxau').setValue(aceselldesignconfirmation);
                    }

					return data[0];
				},
				//scope: this
			},
		},	
	},
	autoLoad: true,
});
