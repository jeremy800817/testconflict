var prevbuyprice = prevsellprice = 0;
var timer = 0;
var timer2 = 0;
var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9'
// var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += 'bo.gtp.development' + '/nchan/sub/2'
Ext.require(['Ext.ux.WebSocketProxy']);
Ext.define('snap.store.OrderPriceStream', {

	extend: 'Ext.data.Store',
	alias: 'store.OrderPriceStream',
	model: 'snap.model.OrderPriceStream',
	storeId: 'orderPriceStream',
	proxy: {
		type: 'websocket',
        storeId: 'orderPriceStream',
		url: websocketurl,	
		reader: {
			type: 'json',
			rootProperty: 'data',
			transform: {
				fn: function (data) {
                    
					var companybuyppg = data[0].companybuy;
                    var companysellppg = data[0].companysell;
                    // var rawfxusdbuy = data[0].rawfxusdbuy;
                    // rawfxusdbuy = parseFloat(rawfxusdbuy).toFixed(3);
                    // var rawfxusdsell = data[0].rawfxusdsell;
                    // rawfxusdsell = parseFloat(rawfxusdsell).toFixed(3);
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
                    // If value > previous green #006400
                    if (parseFloat(companybuyppg) > parseFloat(prevbuyprice)){
                        // Green 
                        colortag = '<h1 style="color:#7ED321;display:inline;text-align:center;">';
                        timer = 0;

                    }else if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)){
                        // If value < previous
                        // Red
                        colortag = '<h1 style="color:#C3262E;display:inline;text-align:center;">';
                        timer = 0;

                    }else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)){
                        // If no change
                        timer++;
                        if (timer > 4){
                            // If no change
                            colortag = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">';
                        }
                        console.log(timer,'timer');
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
                       colortag2 = '<h1 style="color:#7ED321;display:inline;text-align:center;">';
                       timer2 = 0;
                   }else if (parseFloat(companysellppg) < parseFloat(prevsellprice)){
                       // If value < previous
                       // Red
                       colortag2 = '<h1 style="color:#C3262E;display:inline;text-align:center;">';
                       timer2 = 0;
                   }else if (parseFloat(companysellppg) == parseFloat(prevsellprice)){
                       // If no change
                       timer2++;
                        if (timer2 > 4){
                            // If no change
                            colortag2 = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">';
                        }
                   }

                   acesellprice = colortag2 + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">'+ aceselltruncatedright +'</p></h1>';

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
                      Ext.getCmp('acebuypricechange').setValue('green');
                  }else if (parseFloat(companybuyppg) < parseFloat(prevbuyprice)){
                      // If value < previous
                      // Red
                       //backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
                       backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
                      // Get bigger or smaller changes
					  Ext.getCmp('acebuypricechange').setValue('red');
                  }else if (parseFloat(companybuyppg) == parseFloat(prevbuyprice)){
                      // If no change
                      //backgroundcolor = '<div style="padding: 0.7em;background-color:#cccccc;">';
                      backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
                      // Get bigger or smaller changes
					  Ext.getCmp('acebuypricechange').setValue('grey');
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
                    Ext.getCmp('acesellpricechange').setValue('green');

                }else if (parseFloat(companysellppg) < parseFloat(prevsellprice)){
                    // If value < previous
                    // Red
                    //backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
                    backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
                    
                     // Get changes
                     Ext.getCmp('acesellpricechange').setValue('red');
                }else if (parseFloat(companysellppg) == parseFloat(prevsellprice)){
                    // If no changev #cccccc
                    backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
                     // Get changes
                     Ext.getCmp('acesellpricechange').setValue('grey');
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
                    
               
					Ext.getCmp('acesellprice').setValue(companysellppg);
					Ext.getCmp('acebuyprice').setValue(companybuyppg);
                    Ext.getCmp('orderuuid').setValue(data[0].uuid);
                    
                    //Ext.getCmp('bidpricedashboard').setValue(rawfxusdbuy);
                    //Ext.getCmp('askpricedashboard').setValue(rawfxusdsell);

                    prevbuyprice = companybuyppg;
                    prevsellprice = companysellppg;
                    
                    Ext.getCmp('spotacebuy').setValue(acebuydesign);
                    Ext.getCmp('spotacesell').setValue(aceselldesign);

                    // For confirmation fields
                    if (Ext.get('spotorderbuyconfirmationval') != null){
                        Ext.getCmp('spotorderbuyconfirmationval').setValue(acebuydesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('spotorderbuyconfirmationxau') != null){
                        Ext.getCmp('spotorderbuyconfirmationxau').setValue(acebuydesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('spotordersellconfirmationval') != null){
                        Ext.getCmp('spotordersellconfirmationval').setValue(aceselldesignconfirmation);
                    }
                    // For confirmation fields
                    if (Ext.get('spotordersellconfirmationxau') != null){
                        Ext.getCmp('spotordersellconfirmationxau').setValue(aceselldesignconfirmation);
                    }
                    //Ext.getCmp('spotorderbuyconfirmationval').setValue(acebuydesignconfirmation);
                    //Ext.getCmp('spotorderbuyconfirmationxau').setValue(acebuydesignconfirmation);

                    //Ext.getCmp('spotordersellconfirmationval').setValue(aceselldesignconfirmation);
                    //Ext.getCmp('spotordersellconfirmationxau').setValue(aceselldesignconfirmation);

                    

                    
                    
					return data[0];
				},
				//scope: this
			},
		},	
	},
	autoLoad: true,
});
