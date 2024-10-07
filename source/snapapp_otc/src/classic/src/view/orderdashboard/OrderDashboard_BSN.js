// Init searchfield
Ext.define('Ext.ux.form.SearchField', {
    extend: 'Ext.form.field.Trigger',    
    alias: 'widget.searchfield',    
    trigger1Cls: Ext.baseCSSPrefix + 'form-search-trigger',          
    initComponent: function(){
        this.callParent(arguments);
    },      
    afterRender: function(){
        this.callParent();        
    }
});
// End init searchfield

Ext.define('Ext.field.Toggle', {
    extend: 'Ext.slider.Single',

    xtype: 'toggle',
    minValue: 0,
    maxValue: 1,

    width: 30,

    onRender: function(parentNode, containerIdx) {
        this.callParent([parentNode, containerIdx]);
        this.publishValue();
    },

    setValue: function(thumbIndex, value) {
        value = value ? 1 : 0;
        this.callParent([value]);

        this.bodyEl.toggleCls('x-togglefield-on', value);
        this.publishValue();
    },

    getValue: function() {
        return this.callParent([0]) ? true : false;
    }
})

//ViewModel
Ext.define('MyViewModel', {
    extend: 'Ext.app.ViewModel',
    alias: 'viewmodel.OrderDashboardViewModel',
    data: {
        isAdmin: true,
        pricetoggle: 0,
        // BSN_CHANNEL: {
        //     companybuydisplay: 215.15,
        //    companyselldisplay: 120.12
        // }
        convert: {
            product: 'GS-999-9-1g',
            weight: 1,
        },
        input: {
            companybuyamount: 0,
            companybuyxau: 0,
            companysellamount: 0,
            companysellxau: 0
        },
        output: {
            companybuyamount: 0,
            companybuyxau: 0,
            companysellamount: 0,
            companysellxau: 0
        },
        'profile-fullname': '-',
        'profile-id': '-',
        'profile-goldbalance': 0,
        'profile-minbalancexau': 0,
    }
});

// // Store
// Ext.define('snap.store.OrderDashboardTemp', {
//     extend: 'snap.store.Base',
//     model: 'snap.model.OrderDashboardTemp',
//     alias: 'store.OrderDashboardTempStore',
//     autoload: true,
// });

// // Model 
// Ext.define('snap.model.OrderDashboardTemp', {
//     extend: 'snap.model.Base',
//     fields: [
//         {type: 'int', name: 'id'},
//         {type: 'int', name: 'partnerid'},

//         {type: 'string', name: 'partnercusid'},
//         {type: 'string', name: 'email'},
//         {type: 'string', name: 'phoneno'},
//         {type: 'string', name: 'password'},
//         {type: 'string', name: 'oldpassword'},
//         {type: 'string', name: 'preferredlang'},
//         {type: 'string', name: 'fullname'},
//         {type: 'string', name: 'campaigncode'},
//         {type: 'string', name: 'accountholdercode'},
//         {type: 'string', name: 'xaubalance'},
//         {type: 'string', name: 'amountbalance'},
//         {type: 'string', name: 'mykadno'},
//     ]
// });



Ext.define('snap.view.orderdashboard.OrderDashboard_BSN',{
    extend: 'Ext.panel.Panel',
    xtype: 'orderdashboardview_BSN',

    requires: [

        'Ext.layout.container.Fit',
        'snap.view.orderdashboard.OrderDashboardController',
        'snap.view.orderdashboard.OrderDashboardModel',
        'snap.store.OrderPriceStream',


    ],
    // viewModel: {
    //     data: {
    //         name: "Order",
    //         fees: [],
    //         permissions : [],
    //         status: '',

    //     }
    // },
    viewModel: {
        type: 'OrderDashboardViewModel'
    },
    formClass: 'snap.view.gridpanel.GridFormOtc',
    createWebsocket: function(providerCode, channelName){
        // var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+providerCode;


        // env = snap.getApplication().info.env
        // core_socket = '';
        // if (env == 'prod'){
        //     // to be changed later
        //     websocketurl = 'wss://gungho.ace2u.com:8806/socket.io/?EIO=3&transport=websocket';
        // }
        // if (env == 'dev'){
        //     var websocketurl = 'wss://gtp2uat.ace2u.com/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.ONECENT';
        // }
        // var websocketurl = 'wss://otc-uat.ace2u.com:8443/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.PosGold';
        var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+'INTLX.BSN';
        
        Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl,
            //url: 'wss://gtp2.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+providerCode ,
            listeners: {
                open: function (ws) {
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    message = JSON.parse(message);
           
                    //format open gp price
                    let formatDecimal = 3;
                
                    //if (openGP.includes(channelName)) {
                        message.data[0].companybuy = elmnt.formatPrice(message.data[0].companybuy, formatDecimal);
                        message.data[0].companysell = elmnt.formatPrice(message.data[0].companysell, formatDecimal);
                    //}
                    //format price color
                    // Ext.getCmp('acesellprice').setValue(message.data[0].companysell);
					// Ext.getCmp('acebuyprice').setValue(message.data[0].companybuy);
                    // Ext.getCmp('acesellorderuuid').setValue(message.data[0].uuid);
                    // Ext.getCmp('acebuyorderuuid').setValue(message.data[0].uuid);
                    // //{fxr.fxsource}
                    
                    // Ext.getCmp('otc-ace-sell').setValue(message.data[0].companysell);
                    // Ext.getCmp('otc-ace-buy').setValue(message.data[0].companybuy);
                    if (vm.get(channelName)) {
                        Object.keys(message.data[0]).map(function(key, index) {
                            let fields = [
                                'companybuy', 'companysell'
                            ];
                            if(fields.includes(key)){
                                if (vm.get(channelName)[key]) {
                        
                                    appendkey = key + 'display';
                                    message.data[0][appendkey] = elmnt.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);
                                }
                            }
                        });
                    }
             
                    // message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    // message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    // let dateTime = new Date(message.data[0].timestamp * 1000.00);
                    // var dateTimeStr =
                    //     dateTime.getFullYear() + "-" +
                    //     ("00" + (dateTime.getMonth() + 1)).slice(-2) + "-" +
                    //     ("00" + dateTime.getDate()).slice(-2) + " " +
                    
                    //     ("00" + dateTime.getHours()).slice(-2) + ":" +
                    //     ("00" + dateTime.getMinutes()).slice(-2) + ":" +
                    //     ("00" + dateTime.getSeconds()).slice(-2);
                    // message.data[0].datetime = dateTimeStr;
                    vm.set(channelName, message.data[0]); 
                    vm.set('getChannelName', channelName);

                    // Set toggle
					// 0 = Weight is in use
					// 1 = Amount in use
					if(vm.get('pricetoggle') != 1){
						// Do elaborate check
                        // Check the following if inputs are empty
                        
                        // Sell ( If weight not empty, update amount )

                        if(vm.get('input.companysellxau') != 0){
                            custbuyamount = elmnt.calculateAmount(vm.get('input.companysellxau'), vm.get(PROJECTBASE + '_CHANNEL.companysell') );
                            vm.set('output.companysellamount', custbuyamount);
                            // // set input 
                            // vm.set('input.companysellamount', vm.get(PROJECTBASE + '_CHANNEL.companysell') );
                             
                            // Customer buy button when amount is toggled
                            // Check if it exists then hide and show toggle
                            elmnt.toggleOrderPopupButtons(custbuyamount, vm.get('input.companysellxau'),'buy', false);
                        }
                        if(vm.get('input.companybuyxau') != 0){
                            custsellamount = elmnt.calculateAmount(vm.get('input.companybuyxau'), vm.get(PROJECTBASE + '_CHANNEL.companybuy') );
                            vm.set('output.companybuyamount', custsellamount);
                            // // set input 
                            // vm.set('input.companysellamount', vm.get(PROJECTBASE + '_CHANNEL.companysell') );
                            
                            // Customer sell button when amount is toggled
                            // Check if it exists then hide and show toggle
                            elmnt.toggleOrderPopupButtons(custsellamount, vm.get('input.companybuyxau'),'sell', false);
                        }
					}
					else{
						// When weight is disabled
						// Calculate live weights

                        // Sell ( If amount not empty, update weight )
                 
                        if(vm.get('input.companysellamount') != 0){
                            custbuyweight = elmnt.calculateWeight(vm.get('input.companysellamount'), vm.get(PROJECTBASE + '_CHANNEL.companysell') );
                            vm.set('output.companysellxau', custbuyweight);
                            // Do validation check for xau 
                            // validate weight check
      
                            // Customer buy button when amount is toggled
                            // Check if it exists then hide and show toggle
                            elmnt.toggleOrderPopupButtons(vm.get('input.companysellamount'), custbuyweight ,'buy', true);
                           
                            // set input 
                            // vm.set('input.companysellxau', weight);
                        }
                        if(vm.get('input.companybuyamount') != 0){
                            custsellweight = elmnt.calculateWeight(vm.get('input.companybuyamount'), vm.get(PROJECTBASE + '_CHANNEL.companybuy') );
                            vm.set('output.companybuyxau', custsellweight);
                            // validate weight check
                            // set input 
                            // vm.set('input.companysellxau', weight);

                            // Customer sell button when amount is toggled
                            // Check if it exists then hide and show toggle
                            elmnt.toggleOrderPopupButtons(vm.get('input.companybuyamount'), custsellweight ,'sell', true);
                            
                        }
					}
                    
                }
            },
        });
    },

    // New Function for event source
    createPriceEvent: function(channelName, message){

        //format open gp price
        let formatDecimal = 3;
    
        //if (openGP.includes(channelName)) {
        message.data[0].companybuy = elmnt.formatPrice(message.data[0].companybuy, formatDecimal);
        message.data[0].companysell = elmnt.formatPrice(message.data[0].companysell, formatDecimal);
        //console.log(message);
        if (vm.get(channelName)) {
           
            Object.keys(message.data[0]).map(function(key, index) {
                let fields = [
                    'companybuy', 'companysell'
                ];
               
                if(fields.includes(key)){
                    if (vm.get(channelName)[key]) {
                     
                        appendkey = key + 'display';
                        message.data[0][appendkey] = elmnt.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);
                      
                    }
                }
            });
        }

        vm.set(channelName, message.data[0]); 
        vm.set('getChannelName', channelName);
        //vm.set(PROJECTBASE + '_CHANNEL.uuid', message.data[0].uuid);
        // Set toggle
        // 0 = Weight is in use
        // 1 = Amount in use
        if(vm.get('pricetoggle') != 1){
          // Do elaborate check
          // Check the following if inputs are empty
          
          // Sell ( If weight not empty, update amount )

          if(vm.get('input.companysellxau') != 0){
              custbuyamount = elmnt.calculateAmount(vm.get('input.companysellxau'), vm.get(PROJECTBASE + '_CHANNEL.companysell') );
              vm.set('output.companysellamount', custbuyamount);
              // // set input 
              // vm.set('input.companysellamount', vm.get(PROJECTBASE + '_CHANNEL.companysell') );
              
              // Customer buy button when amount is toggled
              // Check if it exists then hide and show toggle
              elmnt.toggleOrderPopupButtons(custbuyamount, vm.get('input.companysellxau'),'buy', false);
          }
          if(vm.get('input.companybuyxau') != 0){
              custsellamount = elmnt.calculateAmount(vm.get('input.companybuyxau'), vm.get(PROJECTBASE + '_CHANNEL.companybuy') );
              vm.set('output.companybuyamount', custsellamount);
              // // set input 
              // vm.set('input.companysellamount', vm.get(PROJECTBASE + '_CHANNEL.companysell') );
              
              // Customer sell button when amount is toggled
              // Check if it exists then hide and show toggle
              elmnt.toggleOrderPopupButtons(custsellamount, vm.get('input.companybuyxau'),'sell', false);
          }
        }
        else{
          // When weight is disabled
          // Calculate live weights

          // Sell ( If amount not empty, update weight )
  
          if(vm.get('input.companysellamount') != 0){
              custbuyweight = elmnt.calculateWeight(vm.get('input.companysellamount'), vm.get(PROJECTBASE + '_CHANNEL.companysell') );
              vm.set('output.companysellxau', custbuyweight);
              // Do validation check for xau 
              // validate weight check

              // Customer buy button when amount is toggled
              // Check if it exists then hide and show toggle
              elmnt.toggleOrderPopupButtons(vm.get('input.companysellamount'), custbuyweight ,'buy', true);
            
              // set input 
              // vm.set('input.companysellxau', weight);
          }
          if(vm.get('input.companybuyamount') != 0){
              custsellweight = elmnt.calculateWeight(vm.get('input.companybuyamount'), vm.get(PROJECTBASE + '_CHANNEL.companybuy') );
              vm.set('output.companybuyxau', custsellweight);
              // validate weight check
              // set input 
              // vm.set('input.companysellxau', weight);

              // Customer sell button when amount is toggled
              // Check if it exists then hide and show toggle
              elmnt.toggleOrderPopupButtons(vm.get('input.companybuyamount'), custsellweight ,'sell', true);
              
          }
        }   
    },
    //End
    formatPrice: function(price, decimal){
        price = parseFloat(price);
        return price.toFixed(decimal);
    },

    formatPriceColor: function(newPrice, oldPrice){
        newPrice = newPrice.toString();
        oldPrice = oldPrice.toString();

        let result = oldPrice.match(/\<span.*\>(.*)\<\/span\>/);

        if (result) {
            oldPrice = result[1];
        }
        // Green
        if (newPrice > oldPrice) {
            return '<span style="color:#1ac69c;">'+newPrice+' ↑</span>';
        }
        // Red
        if (newPrice < oldPrice) {
            return '<span style="color:#FF4848;">'+newPrice+' ↓</span>';
        }
        if (newPrice == oldPrice) {
            return newPrice;
        }
    },

    // end Price stream
    toFixed_norounding: function(n){
        var result = n.toFixed(3);
        return result <= n ? result: (result - Math.pow(0.1,3)).toFixed(3);
    },
    
    // formulas
    // weight formula
    calculateWeight: function (amount, customerinput){
        // Calculate live weights
        // var totalweight = elmnt.toFixed_norounding(amount/ customerinput);
        //debugger;
        totalweight = parseFloat(amount)/ parseFloat(customerinput)
        // return Number(totalweight);
        //debugger;
        return totalweight.toFixed(3);
        
    },

    // amount formula
    calculateAmount: function (weight, customerinput){
        var total = parseFloat(customerinput) * parseFloat(weight);
        //total = Ext.util.Format.number(total, '0,000.00');
        //debugger;
        // Chrome workaround where .5 does not round up
        //debugger;
        //return total;
         return total.toFixed(2);
    },

    // Do Sell Validation
    validateSell: function (total, weight) {

        goldbalance = parseFloat( (vm.get('profile-goldbalance') ? vm.get('profile-goldbalance') : 0) );
        //minbalancexau = parseFloat( (vm.get('profile-minbalancexau') ? vm.get('profile-minbalancexau') : 0) );
        accounttype = vm.get('profile-accounttype');
        minbalancexau = 0;

        // for test
        // goldbalance = parseFloat(4124.111);
        // minbalancexau = parseFloat( 0.02);

        if(accounttype == 'ORGANIS'){
            threshold = 50;
        }else{
            threshold = 10;
        }
        
    
        remainderGold = goldbalance - weight;
		    remainderGold = remainderGold.toFixed(3);
    
        // Check weight and total
        if (goldbalance > minbalancexau){

            // Check if balance sufficient
   
            if (total >= threshold && weight <= goldbalance){
                // Enable button
                return true
            }
            else {
                if(weight >= goldbalance)
                {
                    text = 'Maximum sell amount cannot be higher than '+goldbalance+'gram';
                    // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                    vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
                    return false
                }else if(threshold == 50){
                    // Disable Button
                    text = 'minimum sell amount cannot be lower than RM'+threshold;
                    // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                    vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
                    return false
                }
                
            }

            

        }
        else {
            
            // Disable all
            // document.getElementById('btnpaybank').disabled= true;
            // document.getElementById("btnpaybank").style.opacity= .65;
            // if(goldbalance < minbalancexau && goldbalance >= 0){
            //     text = 'The minimum sell value is '+minbalancexau+'g and you are required to have at least '+minbalancexau+'g in account';
            //     // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
            //     vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            // }else if(remainderGold < minbalancexau){
            //     text = 'The minimum balance cannot lower than '+minbalancexau+'g in account';
            //     // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
            //     vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            // }else{
            //     //debugger;
            //     text = 'Minimum sell amount cannot be lower than RM '+threshold;
            //     // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
            //     vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            // }

            text = 'Balance insufficient';
            vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);


            //debugger;
            return false
            // $('#minbalanceerror').show();
        }
    },

    validateBuy: function (total, weight) {

        goldbalance = parseFloat( (vm.get('profile-goldbalance') ? vm.get('profile-goldbalance') : 0) );
        xaubalance = vm.get('profile-xaubalance');
        minbalancexau = parseFloat( (vm.get('profile-minbalancexau') ? vm.get('profile-minbalancexau') : 0) );
        accounttype = vm.get('profile-accounttype') ? vm.get('profile-accounttype') : "";
        //debugger;
        // for test
        // goldbalance = parseFloat(4124.111);
        // minbalancexau = parseFloat( 0.02);

        if(accounttype == 'ORGANIS'){
            threshold = 50;
        }else{
            threshold = 10;
        }
    
        // remainderGold = goldbalance - weight;
		// remainderGold = remainderGold.toFixed(3);
    
        // Check weight and total
        if(accounttype == 'ORGANIS' && xaubalance == null){
            if (total >= 500){
                // Enable button
                return true
            }
            else {
                text = 'Minimum buy amount cannot be lower than RM500 (First time buy ONLY)';
                vm.get('orderpopup-gridform-buy').lookupController().lookupReference('orderpopup-error-buy').setValue(text);
                // Disable Button
                return false
            }
        }else{
            //debugger;
            if (total >= threshold){
                // Enable button
                return true
            }
            else {
                if(threshold == 50){
                    // Disable Button
                    text = 'minimum buy amount cannot be lower than RM'+threshold;	
                    vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-buy').setValue(text);
                    return false
                }else{
                    // Disable Button
                    return false
                }
                
            }
        }

    },

    // check and toggle buttons popup
    // togglebutton func
    // input reference name for viewmodel and function will search
    toggleOrderPopupButtons: function (amount, weight, type, toggle){
        // check if viewmodel exists
        // debugger;
        if(vm.get('orderpopup-gridform-' + type)){
            // do something
            // button = vm.get(type);

            // do form validation, if weight do weight check, if amount do amount check
            // check type customer buy or customer sell
            switch(type){
                case 'buy':
                    result = this.validateBuy(amount, weight);
                    break;
                case 'sell':
                    
                    result = this.validateSell(amount, weight);
                    break;
                default: 

            }
            // result = this.validateBuySell(amount, weight);
            // return true or false,
            // Toggle legend
            // True = $(#total), calculate weight
            // False = $(#weight), calculate total
            if(result){
                setStyle = '1';
                setDisabled = false;
                vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setHidden(true);
            }else{
                setStyle = '0.5';
                setDisabled = true;
                vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setHidden(false);
            }
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setValue('poki');
       
            
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-button-'+ type).setHidden(toggle);
            vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-button-'+ type).setStyle('opacity', setStyle);
            vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-button-'+ type).setDisabled(setDisabled);

        }
      
    },

    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vm = this.getViewModel();

        // Ext.create('snap.store.OrderPriceStream');
        async function getList(){
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )
        //const source = new EventSource('https://otc-uat.ace2u.com:8443/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.BSN');
        const source = new EventSource('https://' + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.BSN');
        source.onmessage = function(event) {
            // handle the message received from the server
            jsonString  = event.data;
            // sample format below
            // '{"event":"read","data":[{"companybuy":287.471222,"companysell":295.568383,"uuid":"PS00000000005B7581","timestamp":1683098630}]}'
            jsonObj = JSON.parse(jsonString);
        
            elmnt.createPriceEvent(PROJECTBASE + "_CHANNEL", jsonObj);
            
        };
        //this.createWebsocket('INTLX.GTP_T1', PROJECTBASE + "_CHANNEL");

        this.callParent(arguments);
    },
    permissionRoot: '/root/gtp/cust',
    //store: { type: 'Order' },
    store: 'orderPriceStream',	
    controller: 'orderdashboard-orderdashboard',
    // formDialogWidth: 950,
    layout: 'fit',
    // width: 500, 
    // height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    //bodyPadding: 25,


    items: {
        
        
        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
            {
                // title: 'Summary',
                height: 30,
                minHeight: 75,
                maxHeight: 800,
                layout: {
                    type: 'hbox',
                },
                margin: "10 0 0 0",
                defaults: {
                    bodyStyle: 'padding:0px;margin-top:10px',
                },
                cls: 'otc-main-center search_bar',
                // Size is 24 blocks spread across 3 screens
                items:[
                    { 
                        flex:1,
                        xtype:'combobox',
                        cls:'combo_box',
                        store: {
                            fields: ['type', 'name'],
                            data : [
                                {"type":"", "name":""},
                                {"type":"1", "name":"Customer ID"},
                                {"type":"2", "name":"MyKad No"},
                                {"type":"2", "name":"Passport Number"},
                                {"type":"2", "name":"Company Registration No"},
                                {"type":"4", "name":"GIRO/ GIRO i Account No"},
                                
                            ]
                        },
                        listeners: {
                            select: function(combo, records, eOpts) {
                                accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                                if(records.data.name == 'MyKad No'){
                                    newText = "Enter " + records.data.name + " here (without alphabet or '-')";
                                }else{
                                    newText = "Enter " + records.data.name + " here";
                                }
                                accountholdersearch.setEmptyText(newText);
                                // this.up().up().up().getController().lookupReference('casasearchtype').setValue(records.data.type);
                            }
                        },
                        reference: 'casasearchtype',
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'type',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                    },
                    {   
                        // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                        // header: {
                        //     style: {
                        //         backgroundColor: 'white',
                        //         display: 'inline-block',
                        //         color: '#000000',
                                
                        //     }
                        // },
                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        //title: 'Ask',
                        flex: 4,
                        margin: '0 10 0 0',
                        items: [ {
                            xtype: 'textfield',
                            text: 'Search',
                            emptyText: '',
                            flex:1,
                            style: 'text-align:center;',
                            width: '90%',
                            reference: 'accountholdersearch',
                       
                        //     listeners: {
                        //         'change' : function(field, value, oldvalue, eOpts) {                    
                        //              this.store.load({params:{id: 1,search: value}});
                        //         },
                        //         onAfter : function(eventName, fn, scope, options) {
                        //             debugger;
                        //              this.store.load({params:{id: 1,search: value}});
                        //         },
                        //         scope:this,
                        //    }
                        }]
                    },

                    // { 
                    //     flex:1,
                    //     xtype:'combobox',
                    //     cls:'combo_box',
                    //     store: {
                    //         fields: ['abbr', 'name'],
                    //         data : [
                    //             {"abbr":"", "name":""},
                    //             {"abbr":"ICNO", "name":"Identity Card Number"},
                    //             {"abbr":"ACCNO", "name":"Account Number"},
                    //             {"abbr":"CRNO", "name":"Company Registration Number"}
                                
                    //         ]
                    //     },
                    //     queryMode: 'local',
                    //     displayField: 'name',
                    //     valueField: 'abbr',
                    //     forceSelection: true,
                    //     editable: false,
                    //     margin: "0 10 0 10",
                    // },
                    
                    {   
                        // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                        // header: {
                        //     style: {
                        //         backgroundColor: 'white',
                        //         display: 'inline-block',
                        //         color: '#000000',
                                
                        //     }
                        // },
                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        //title: 'Ask',
                      
                        flex:1,
                        xtype:'button',
                        text:'SEARCH',
                        iconCls: 'x-fa fa-search',
                        cls:'search_btn',
                        handler:'',
                        margin: "0 0 0 10",
                        handler: 'searchAccountHolder'
                    },
              
               
                ]

            },
            {
                xtype: 'panel',
                title: 'Search Results <span font-size: 5px;>(Please select a record to view data)</span>',
                reference: 'searchresults',
                border: false,
                hidden: true,
                margin: "10 0 0 0",
                
                items: [
                    {
                        title: '',
                        flex: 13,
                        xtype: 'myaccountholdersearchresultview',
                        reference: 'myaccountholdersearchresults',
                        
                        enablePagination: true,
                        store: {
                            proxy: {
                                type: 'ajax',
                                url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                        },
                        columns: [
                            { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
                            //{ text: 'Amount Balance', dataIndex: 'amountbalance',exportdecimal:2, filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00') },
                            { text: 'Gold Account No', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Partner Code', dataIndex: 'partnercode', hidden: true, filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Partner', dataIndex: 'partnername', hidden: true, filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'My Kad / Passport No', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Customer ID', dataIndex: 'partnercusid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Phone Number', dataIndex: 'phoneno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Preferred Lang', dataIndex: 'preferredlang', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Occupation', dataIndex: 'occupation', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Occupation Category ID', dataIndex: 'occupationcategoryid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Salesperson Code', dataIndex: 'referralsalespersoncode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Branch Code', dataIndex: 'referralbranchcode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Branch Name', dataIndex: 'referralbranchname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            //{ text: 'Pin Code', dataIndex: 'pincode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                            { text: 'SAP Buy Code', dataIndex: 'sapacebuycode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                            { text: 'SAP Sell Code', dataIndex: 'sapacesellcode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                            { text: 'Bank Name', dataIndex: 'bankname', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                            { text: 'Bank Account Name', dataIndex: 'accountname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'GIRO/ GIRO i Account No', dataIndex: 'accountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Account Type', dataIndex: 'accounttype',   filter: {
                                type: 'combo',
                                store: [
                                    ['1', 'Sendiri'],
                                    ['2', 'Bersama'],
                                    ['3', 'Organis'],
                                    ['4', 'Amanah'],
                                    ['5', 'Unknown'],
                                    ['6', 'Cashless'],
                                    ['7', 'Cashlne'],
                                ],
                            },
                            renderer: function (value, rec) {
                                if (value == '1') return 'Sendiri';
                                else if (value == '2') return 'Bersama';
                                else if (value == '3') return 'Organis';
                                else if (value == '4') return 'Amanah';
                                else if (value == '5') return 'Unknown';
                                else if (value == '6') return 'Cashless';
                                else if (value == '7') return 'Cashlne';
                                else return 'Unidentified';
                            }, minWidth: 130, flex: 1, },
                            { text: 'Secondary Full Name', dataIndex: 'nokfullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Secondary Mykad No', dataIndex: 'nokmykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            //{ text: 'Secondary Bank Name', dataIndex: 'nokbankname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            //{ text: 'Secondary Account No', dataIndex: 'nokaccountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Investment Made', dataIndex: 'investmentmade',   filter: {
                                type: 'combo',
                                store: [
                                    ['0', 'No'],
                                    ['1', 'Yes'],
                                ],
                            },
                            renderer: function (value, rec) {
                                if (value == '0') return 'No';
                                else if (value == '1') return 'Yes';
                                else return 'Unidentified';
                            }, hidden: true, minWidth: 130, flex: 1, },
                            { text: 'Xau Balance', dataIndex: 'xaubalance', exportdecimal:3, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000') },
                            // { 
                            //     text: 'loan total', dataIndex: 'loantotal', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                            //     editor: {    //field has been deprecated as of 4.0.5
                            //         xtype: 'numberfield',
                            //         decimalPrecision: 3
                            //     } 
                            // },
                            // { 
                            //     text: 'loan balance', dataIndex: 'loanbalance', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                            //     editor: {    //field has been deprecated as of 4.0.5
                            //         xtype: 'numberfield',
                            //         decimalPrecision: 3
                            //     } 
                            // },
                            // { text: 'Loan approved on', dataIndex: 'loanapprovedate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
                            // { text: 'Approved by', dataIndex: 'loanapproveby', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            // {
                            //     text: 'Loan Status', dataIndex: 'loanstatus', minWidth: 100,
                            //     filter: {
                            //         type: 'combo',
                            //         store: [
                            //             ['0', 'No'],
                            //             ['1', 'Approved'],
                            //             ['2', 'Settled'],
                            //         ],
                            //     },
                            //     renderer: function (value, rec) {
                            //         if (value == '0') return 'No';
                            //         else if (value == '1') return 'Approved';
                            //         else if (value == '2') return 'Settled';
                            //         else return 'Unidentified';
                            //     },
                            // },
                            // { text: 'Reference Number', dataIndex: 'loanreference', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            {
                                text: 'Is PEP', dataIndex: 'ispep', minWidth: 100,
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'No'],
                                        ['1', 'Yes'],
                                    ],
                                },
                                renderer: function (value, rec) {
                                    if (value == '0') return 'No';
                                    else if (value == '1') return 'Yes';
                                    else return 'Unidentified';
                                },
                            },
                            { text: 'Pep Declaration', dataIndex: 'pepdeclaration', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            {
                                text: 'Status', dataIndex: 'status', minWidth: 100,
                    
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Inactive'],
                                        ['1', 'Active'],
                                        ['2', 'Suspended'],
                                        ['4', 'Blacklisted'],
                                        ['5', 'Closed'],
                    
                                    ],
                    
                                },
                                renderer: function (value, rec) {
                                    if (value == '0') return '<span data-qtitle="Inactive" data-qwidth="200" '+
                                    'data-qtip="Account Pending Email Activation">'+
                                     "Inactive" +'</span>';
                                    else if (value == '1') return '<span data-qtitle="Active" data-qwidth="200" '+
                                    'data-qtip="Active Accounts">'+
                                     "Active" +'</span>';
                                    else if (value == '2') return '<span data-qtitle="Suspended" data-qwidth="200" '+
                                    'data-qtip="Accounts Pending Closure Approval">'+
                                     "Suspended" +'</span>';
                                    else if (value == '4') return '<span data-qtitle="Blacklisted" data-qwidth="200" '+
                                    'data-qtip="Blacklisted Accounts">'+
                                     "Blacklisted" +'</span>';
                                    else if (value == '5') return '<span data-qtitle="Closed" data-qwidth="200" '+
                                    'data-qtip="Accounts Successfully Closed">'+
                                     "Closed" +'</span>';
                                    else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                                    'data-qtip="Unknown Status">'+
                                     "Unidentified" +'</span>';
                                },
                                // renderer: function (value, rec) {
                                //     if (value == '0') return 'Inactive';
                                //     else if (value == '1') return 'Active';
                                //     else if (value == '2') return 'Suspended';
                                //     else if (value == '4') return 'Blacklisted';
                                //     else if (value == '5') return 'Closed';
                                        
                                //     else return 'Unidentified';
                                // },
                            },
                            {
                                text: 'PEP Status', dataIndex: 'pepstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Pending'],
                                        ['1', 'Passed'],
                                        ['2', 'Failed'],
                                    ],
                                },
                                renderer: function (val, m, record) {
                                    // If PEP
                                    if (record.data.ispep == 1) {
                                        if (record.data.pepstatus == 0) {
                                            // PEP Status Pending
                                            return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                                        } else if (record.data.pepstatus == 1) {
                                            // PEP Status Passed
                                            return '<span class="fa fa-circle x-color-success"></span>';
                                        } else if (record.data.pepstatus == 2) {
                                            // PEP Status Failed
                                            return '<span class="fa fa-circle x-color-danger"></span>';
                                        } 
                                    } else {
                                        // PEP Status Unidentified
                                        return '<span class="fa fa-circle x-color-default"></span>';
                                    }
                                }
                            },
                            {
                                text: 'Is KYC Manually Approved', dataIndex: 'iskycmanualapproved', minWidth: 100,
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'No'],
                                        ['1', 'Yes'],
                                    ],
                                },
                                renderer: function (value, rec) {
                                    if (value == '0') return 'No';
                                    else if (value == '1') return 'Yes';
                                    else return 'Unidentified';
                                },
                            },
                            {
                                text: 'KYC Status', dataIndex: 'kycstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Incomplete'],
                                        ['1', 'Passed'],
                                        ['2', 'Pending'],
                                        ['7', 'Failed'],
                                    ],
                                },
                                renderer: function (val, m, record) {
                    
                                    if (record.data.kycstatus == 0) {
                                        // eKYC Status Incomplete
                    
                                        if (record.data.kycpastday == false) {
                                            return '<span class="fa fa-circle x-color-default"></span>';
                                        } else {
                                            return '<span class="fa fa-circle x-color-warning"></span>';
                                        }
                                    } else if (record.data.kycstatus == 1) {
                                        // eKYC Status Passed
                                        return '<span class="fa fa-circle x-color-success"></span><span>';
                                    } else if (record.data.kycstatus == 2) {
                                        // eKYC Status Pending
                                        return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                    
                                    } else if (record.data.kycstatus == 7) {
                                        // eKYC Status Failed
                                        return '<span class="fa fa-circle x-color-danger"></span><span>';
                                    } else {
                                        // eKYC Status Unidentified
                                        return '<span class="fa fa-circle x-color-default"></span><span>';
                                    }
                                }
                            },
                    
                            //{ text: 'Amla Status',  dataIndex: 'amlastatus', filter: {type: 'string'} , minWidth:130, flex: 1 },
                            {
                                text: 'AMLA Status', dataIndex: 'amlastatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Pending'],
                                        ['1', 'Passed'],
                                        ['2', 'Failed'],
                                    ],
                                },
                                renderer: function (val, m, record) {
                                    // If KYC pass
                                    if (record.data.kycstatus == 1) {
                                        if (record.data.amlastatus == 0) {
                                            // AMLA Status Pending
                                            return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                                        } else if (record.data.amlastatus == 1) {
                                            // AMLA Status Passed
                                            return '<span class="fa fa-circle x-color-success"></span><span>';
                                        } else if (record.data.amlastatus == 2) {
                                            // AMLA Status Failed
                                            return '<span class="fa fa-circle x-color-danger"></span><span>';
                                        } 
                                    } else {
                                        // AMLA Status Unidentified
                                        return '<span class="fa fa-circle x-color-default"></span><span>';
                                    }       
                                }
                            },
                            { text: 'Status Remarks', dataIndex: 'statusremarks', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            {
                                text: 'Dormant', dataIndex: 'dormant', filter: { type: 'string' }, minWidth: 100, align: 'center',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['1', 'Yes'],
                                        ['0', 'No'],                    
                                    ],
                                },
                                renderer: function (val, m, record) {
                                    if (record.data.dormant) {
                                        return '<span class="fa fa-circle x-color-danger"></span><span>';
                                    } else {                    
                                        return '<span class="fa fa-circle x-color-success"></span><span>';
                                    }       
                                }
                            },
                            { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            { text: 'Password Modified', dataIndex: 'passwordmodified', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                            { text: 'Last Login on', dataIndex: 'lastloginon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                            { text: 'Last Login IP', dataIndex: 'lastloginip', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                            { text: 'Verified on', dataIndex: 'verifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
                    
                            { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
                            { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
                            { text: 'Created by', dataIndex: 'createdbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                            { text: 'Modified by', dataIndex: 'modifiedbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                    
                        ],
                        viewConfig : {
                            listeners : {
                                cellclick : function(view, cell, cellIndex, record,row, rowIndex, e) {

                                    // Store information to Viewmodel
                                    vm.set('profile-fullname', record.data.fullname);
                                    vm.set('profile-accountholdercode', record.data.accountholdercode);
                                    vm.set('profile-accounttype', record.data.accounttypestr);
                                    vm.set('profile-accountnumber', record.data.accountnumber);
                                    vm.set('profile-id', record.data.id);
                                    vm.set('profile-xaubalance', record.data.xaubalance);
                                    
                                    // Set profile info 
                                    vm.set('profile-mykadno', record.data.mykadno);
                                    vm.set('profile-email', record.data.email);
                                    vm.set('profile-phoneno', record.data.phoneno);
                                    vm.set('profile-amountbalance', record.data.amountbalance);

                                    // Set more informatiosn
                                    vm.set('profile-goldbalance', record.data.goldbalance);
                                    vm.set('profile-avgbuyprice', record.data.avgbuyprice);
                                    vm.set('profile-totalcostgoldbalance', record.data.totalcostgoldbalance);
                                    vm.set('profile-diffcurrentpriceprcetage', record.data.diffcurrentpriceprcetage);
                                    vm.set('profile-currentgoldvalue', record.data.currentgoldvalue);
                                    vm.set('profile-availablebalance', record.data.availablebalance);
                                    vm.set('profile-minbalancexau', record.data.minbalancexau);
                                    vm.set('profile-address', record.data.address ? record.data.address : '-');

                                    //debugger;
                                    // set conversion values
                                    // balanceafterconversion = record.data.goldbalance - elmnt.lookupReference('totalconversionvalue').value;
                                    // balanceafterconversion = parseFloat(balanceafterconversion).toFixed(3);
                                    // elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion: 0.000);
                                    
                                    // Get data and populate profile details profiledetails
                                    var getDisplayController = this.up().up().up().up().getController();

                                    // If image is found (disabled for bsn)
                                    // if(record.data.images){
                                    //     // Fill image
                                    //     getDisplayController.lookupReference('profile-front-image').setHtml(record.data.images.front_image);
                                        
                                    //     // Show loaded image
                                    //     getDisplayController.lookupReference('profile-front-image').setHidden(false);

                                    //     // hide default image and show loaded image
                                    //     getDisplayController.lookupReference('profile-front-image-default').setHidden(true);
                                    //     // getDisplayController.lookupReference('profile-back-image').setHtml(record.data.images.back_image);
                                        
                                    // }else{
                                    //     getDisplayController.lookupReference('profile-front-image').setHtml('');
                                    //     // getDisplayController.lookupReference('profile-back-image').setHtml('');
                                    //     // Hide loaded image
                                    //     getDisplayController.lookupReference('profile-front-image').setHidden(true);

                                    //     // show default image and show loaded image
                                    //     getDisplayController.lookupReference('profile-front-image-default').setHidden(false);
                                    //     // getDisplayController.lookupReference('profile-back-image').setHtml(record.data.images.back_image);
                                        
                                    // }
                                    getDisplayController.lookupReference('profile-fullname').setValue(record.data.fullname);
                                    getDisplayController.lookupReference('profile-occupationcategory').setValue(record.data.occupationcategory);
                                    getDisplayController.lookupReference('profile-mykadno').setValue(record.data.mykadno);
                                    getDisplayController.lookupReference('profile-email').setValue(record.data.email);
                                    getDisplayController.lookupReference('profile-phoneno').setValue(record.data.phoneno);

                                    getDisplayController.lookupReference('profile-address').setValue(record.data.address);
                                    getDisplayController.lookupReference('profile-status').setValue(record.data.statusname);
                                    
                                    getDisplayController.lookupReference('profile-goldbalance').setValue((record.data.goldbalance ? record.data.goldbalance : 0)+ 'g');
                                    getDisplayController.lookupReference('profile-avgbuyprice').setValue('RM' + (record.data.avgbuyprice ? record.data.avgbuyprice : 0)+ '/g');
                                    getDisplayController.lookupReference('profile-totalcostgoldbalance').setValue('RM' + (record.data.totalcostgoldbalance ? record.data.totalcostgoldbalance : 0));
                                    getDisplayController.lookupReference('profile-diffcurrentpriceprcetage').setValue((record.data.diffcurrentpriceprcetage ? record.data.diffcurrentpriceprcetage : 0) + '%');
                                    getDisplayController.lookupReference('profile-currentgoldvalue').setValue('RM' + (record.data.currentgoldvalue ? record.data.currentgoldvalue : 0));

                                    // Additional fields
                                    getDisplayController.lookupReference('profile-accountnumber').setValue(record.data.accountnumber);
                                    getDisplayController.lookupReference('profile-partnercusid').setValue(record.data.partnercusid);
                                    getDisplayController.lookupReference('profile-partnername').setValue(record.data.partnername);
                                    getDisplayController.lookupReference('profile-accounttype').setValue(record.data.accounttypestr);
                                    getDisplayController.lookupReference('profile-accountbalance').setValue(record.data.accountbalance);

                                    // Get transaction
                                    ordersearchgrid = getDisplayController.lookupReference('myorder');
                                    //ordersearchgrid.getStore().proxy.url = 'https://10.10.55.114/index.php?hdl=myorder&action=getOtcOrders&mykadno='+record.data.mykadno+'&partnerid='+record.data.partnerid+'&accountholdercode='+record.data.accountholdercode;
                                    ordersearchgrid.getStore().proxy.url = 'index.php?hdl=myorder&action=list&partnercode='+PROJECTBASE+'&mykadno='+record.data.mykadno+'&partnerid='+record.data.partnerid+'&accountholdercode='+record.data.accountholdercode;
                                    ordersearchgrid.getStore().reload();

                                    //Get Conversion records
                                    conversionsearchgrid = getDisplayController.lookupReference('myconversion');
                                    //ordersearchgrid.getStore().proxy.url = 'https://10.10.55.114/index.php?hdl=myorder&action=getOtcOrders&mykadno='+record.data.mykadno+'&partnerid='+record.data.partnerid+'&accountholdercode='+record.data.accountholdercode;
                                    conversionsearchgrid.getStore().proxy.url = 'index.php?hdl=myconversion&action=list&partnercode='+PROJECTBASE+'&id='+record.data.id;
                                    conversionsearchgrid.getStore().reload();
									
									//Get Transfer Gold records
                                    transfergoldsearchgrid = getDisplayController.lookupReference('mytransfergold');
                                    transfergoldsearchgrid.getStore().proxy.url = 'index.php?hdl=mytransfergold&action=list&partnercode='+PROJECTBASE+'&id='+record.data.id;
                                    transfergoldsearchgrid.getStore().reload();

                                    Ext.Msg.show({
                                        title: 'Successful',
                                        message: 'Customer Account is selected.',
                                        // buttons: Ext.Msg.YESNOCANCEL,
                                        buttons: Ext.Msg.YES,
                                        
                                    });
                             
                                    //   var clickedDataIndex = view.panel.headerCt.getHeaderAtIndex(cellIndex).dataIndex;
                                    //   var clickedColumnName = view.panel.headerCt.getHeaderAtIndex(cellIndex).text;
                                    //   var clickedCellValue = record.get(clickedDataIndex);
                                  }
                             }
                        }
                        
                        // store: {
                        //     type: 'MyAccountHolder',
                        //     proxy: {
                        //         type: 'ajax',
                        //         url: 'index.php?hdl=myaccountholder&action=list&partnercode=GO',
                        //         reader: {
                        //             type: 'json',
                        //             rootProperty: 'records',
                        //         }
                        //     },
                        // },
                    

                    },
                ],
            },
            // End Search
            {
                xtype: 'panel',
                title: 'Profile Details',
                layout: 'hbox',
                collapsible: true,
                cls: 'otcpanel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                reference: 'profiledetails',
                items: [
                  {
                    defaultType: 'displayfield',
                    defaults: {
                      labelStyle: 'font-weight:bold',
                    },
                    
                    style:'margin-left: 5px',
                    items: [
                      {
                        fieldLabel: 'Full Name',
                        name: 'fullname',
                        reference: "profile-fullname"
                      },                
                      {
                        fieldLabel: 'Occupation Category',
                        name: 'occupationcategory',
                        reference: "profile-occupationcategory"
                      },       
                      {
                        fieldLabel: 'My Kad / Passport No',
                        name: 'mykadno',
                        // value: theData.information.mykadno.slice(0, 6) + '-' + theData.information.mykadno.slice(6, 8) + '-' + theData.information.mykadno.slice(-4)
                        reference: "profile-mykadno"
                      },
                      {
                        fieldLabel: 'Email',
                        name: 'email',
                        reference: "profile-email"
                      },
                      {
                        fieldLabel: 'Phone Number',
                        name: 'phoneno',
                        reference: "profile-phoneno"
                      },
                      {
                    
                        fieldLabel: 'Address',
                        name: 'address',
                        reference: "profile-address",
                        // value: "Tower 1 @ PFCC, Jalan Puteri 1/2, Bandar Puteri, 47100 Puchong, Selangor",
                        width : '80%'
                      },   

                    ]
                  },

                  {
                    defaultType: 'displayfield',
                    defaults: {
                      labelStyle: 'font-weight:bold',
                    },
                    items: [
                      {
                        fieldLabel: 'Customer ID',
                        name: 'partnercusid',
                        reference: "profile-partnercusid"
                      },                
                      {
                        fieldLabel: 'GIRO/ GIRO i Account No',
                        name: 'accountnumber',
                        reference: "profile-accountnumber"
                      },       
                      {
                        fieldLabel: 'Account Type',
                        name: 'accounttype',
                        reference: "profile-accounttype"
                      },
                      {
                        fieldLabel: 'Branch Registered',
                        name: 'partnername',
                        // value: theData.information.mykadno.slice(0, 6) + '-' + theData.information.mykadno.slice(6, 8) + '-' + theData.information.mykadno.slice(-4)
                        reference: "profile-partnername"
                      },
                      {
                        fieldLabel: 'Status',
                        name: 'status',
                        reference: "profile-status"
                      },
                      {
                        fieldLabel: 'Bank Account Balance',
                        name: 'accountbalance',
                        reference: "profile-accountbalance"
                      },
                    ]
                  },
                ]
      
            },
            
            {
                xtype: 'container',
                scrollable: false,
                layout: {
                    type: 'hbox',
                    align: 'stretch',
                },
                defaults: {
                    bodyPadding: '20',
                    // border: true
                },
                // cls: 'otc-container',
                style: {
                    //backgroundColor: '#204A6D',
                    borderColor: '#red',
                },
                margin: '10 0 0 0',
                // height: '40%',
                autoheight: true,
                items: [
                {
                    xtype: 'form',
                    title: 'Sell Order',
                    reference: 'sellorder-form',
                    id: 'orderdashboardspotorderform',
                    cls: 'otc-main-center buysell_modal',
                    header: false,
                    border: true,
                    autoHeight: true,
                    flex: 13,
                    padding : '0 5 0 0',
                    align: 'stretch',
                    listeners: {
                        afterrender: function(form) {
                          var hasSellGoldPermission = snap.getApplication().hasPermission('/root/bsn/search/sell');
                          settings = !hasSellGoldPermission; // reverse variable
                          // Update the hidden property based on the variable
                          form.setHidden(settings);
                        }
                    },
                    items: [
                        {
                            title: 'Sell Price',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-center-price-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        // {
                                        //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                        //     width: '100%',
                                        // },
                                        {
                                            xtype: 'hiddenfield', id: 'acebuyprice', 
                                            value: '0.00',
                                            // bind: {
                                            //     value: '{'+PROJECTBASE + '_CHANNEL.companybuy}',
                                            // },
                                            name:'acebuyprice', reference: 'acebuyprice', fieldLabel: 'Ace Buy Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        },
                                        {
                                            xtype: 'hiddenfield', id: 'acebuyorderuuid',
                                            value: '-', 
                                            // bind: {
                                            //     value: '{'+PROJECTBASE + '_CHANNEL.uuid}', 
                                            // },
                                            name:'uuid', reference: 'uuid', fieldLabel: 'UUID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        },
                                        {
                                            xtype: 'displayfield',
                                            id: "otc-ace-buy",
                                            // value: '3.98g',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-order-buysell',
                                            value: '0.00',
                                            bind: {
                                                value: '{'+PROJECTBASE + '_CHANNEL.companybuydisplay}', 
                                            },
                                            
                                            // listeners:{
                                            //     change:function(thisCmp,newValue,oldValue){
                                                   
                                            //         if(oldValue){
                                                
                                            //             if (parseFloat(newValue) > parseFloat(oldValue)){
                                            //                 // Green 
                                            //                 Ext.getCmp('acesellpricechange').setValue('green');
                                        
                                            //             }else if (parseFloat(newValue) < parseFloat(oldValue)){
                                            //                 // If value < previous
                                            //                 // Red
                                            //                 // Get changes
                                            //                 Ext.getCmp('acesellpricechange').setValue('red');
                                            //             }else if (parseFloat(newValue) == parseFloat(oldValue)){
                                            //                 // If no changev #cccccc
                                            //                 // Get changes
                                            //                 Ext.getCmp('acesellpricechange').setValue('grey');
                                            //             }
                                            //         }else{
                                            //             // Set initial value 
                                            //             debugger;
                                            //         }
                                            //          Ext.getCmp('textfieldid').setDisabled(newValue);
                                            //          if(newValue==true){
                                            //             Ext.getCmp('otc-ace-buy').addCls('otc-displayfield-order-buysell-down');
                                            //         } else {
                                            //             Ext.getCmp('otc-ace-buy').removeCls('otc-displayfield-order-buysell-down');
                                            //         }
                                            //     }
                                            // }
                                        },
                                        {
                                            xtype: 'displayfield',
                                            value: 'PER GRAM',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-small-text',
                    
                                        },
    
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],
                    dockedItems: [{
                        xtype: 'toolbar',
                        dock: 'bottom',
                        // style: 'opacity: 1.0;background: #ffffff;color: #ffffff; border-color: #ffffff; display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',      
                        //ui: 'footer',
                        // defaults: {
                        //     // align: 'right',
                        //     buttonAlign: 'right',
                        //     alignTo: 'right',
                        // },
                        // // defaultAlign: 'right',
                        // buttonAlign: 'right',
                        // alignTo: 'right',
                        layout: {
                            pack: 'center',
                            type: 'hbox',
                            // align: 'right'
                        },
                        cls: 'otc-main-center-price-button-sell',
                        //style: "background-color: gray;", // TEMP FUNCTION TO REMOVE
                        items: [{
                            text: 'Bank Buy',
                            handler: '',
                            //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                            // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                            // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                            flex: 4,
                            tooltip: 'Sell Gold',
                            reference: 'Sell Now',
                            handler: 'doSpotOrderSellOTC',
                            // style: "background-color: gray;", // TEMP FUNCTION TO REMOVE
                            
                        }],
                    }]
                },
                
                {
                    xtype: 'form',
                    title: 'Buy Price',
                    reference: 'buyorder-form',
                    // id: 'orderdashboardfutureorderform',
                    cls: 'otc-main-center buysell_modal',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    // header: {
                    //     // Custom style for Migasit
                    //     /*style: {
                    //         backgroundColor: '#204A6D',
                    //     },*/
                    //     style : 'background-color: #204A6D;border-color: #204A6D;',
                    //     titlePosition: 0,
                    //     items: [{
                    //         xtype: 'button',
                    //         //text: 'Offline',
                    //         //style: 'background-color: #C0282E;border-radius: 20px;border-color: #204A6D;'
                    //         style: 'background-color: #204A6D;border-radius: 20px;border-color: #204A6D;'
                    //     }]
                    // },
                    border: false,
                    listeners: {
                        afterrender: function(form) {
                          var hasBuyGoldPermission = snap.getApplication().hasPermission('/root/bsn/search/buy');
                          settings = !hasBuyGoldPermission; // reverse variable
                          // Update the hidden property based on the variable
                          form.setHidden(settings);
                        }
                    },

                    items: [
                        {
                            title: 'Buy Price',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-center-price-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        // {
                                        //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                        //     width: '100%',
                                        // },
                                        {
                                            xtype: 'hiddenfield', id: 'acesellprice', 
                                            value: '0.00',
                                            // bind: {
                                            //     value: '{'+PROJECTBASE + '_CHANNEL.companysell}',
                                            // },
                                            name:'acesellprice', reference: 'acesellprice', fieldLabel: 'Ace Sell Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        },
                                        {
                                            xtype: 'hiddenfield', id: 'acesellorderuuid', 
                                            value: '-',
                                            // bind: {
                                            //     value: '{'+PROJECTBASE + '_CHANNEL.uuid}', 
                                            // },
                                            name:'uuid', reference: 'uuid', fieldLabel: 'UUID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        },
                                        {
                                            xtype: 'displayfield',
                                            id: "otc-ace-sell",
                                            // value: '3.98g',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-order-buysell',
                                            value: '0.00',
                                            bind: {
                                                value: '{'+PROJECTBASE + '_CHANNEL.companyselldisplay}',
                                            },
                                             
                                            // listeners:{
                                            //     change:function(thisCmp,newValue,oldValue){
                                            //         // debugger;
                                            //          Ext.getCmp('textfieldid').setDisabled(newValue);
                                            //          if(newValue==true){
                                            //             Ext.getCmp('otc-ace-sell').addCls('otc-displayfield-order-buysell-down');
                                            //         } else {
                                            //             Ext.getCmp('otc-ace-sell').removeCls('otc-displayfield-order-buysell-down');
                                            //         }
                                            //     }
                                            // }
                                        },
                                        {
                                            xtype: 'displayfield',
                                            value: 'PER GRAM',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-small-text',
                    
                                        },
    
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    dockedItems: [{
                        xtype: 'toolbar',
                        dock: 'bottom',
                        //ui: 'dark',
                        // style: 'opacity: 1.0;background: #ffffff;color: #ffffff; border-color: #ffffff; display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',      
                       
                        // defaults: {
                        //     // align: 'right',
                        //     buttonAlign: 'right',
                        //     alignTo: 'right',
                        // },
                        // // defaultAlign: 'right',
                        // buttonAlign: 'right',
                        // alignTo: 'right',
                        layout: {
                            pack: 'center',
                            type: 'hbox',
                            // align: 'right'
                        },
                        cls: 'otc-main-center-price-button-buy',
                        // style: "background-color: gray;", // TEMP FUNCTION TO REMOVE
                        items: [{
                                    text: 'Bank Sell',
                                    handler: '',
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                    // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                    flex: 4,
                                    tooltip: 'Buy Gold',
                                    reference: 'Buy Now',
                                    handler: 'doSpotOrderBuyOTC',
                                    // style: "background-color: gray;", // TEMP FUNCTION TO REMOVE
                                }],
                    }],
                }]

             // id: 'medicalrecord',
            }, 
            // End cust gold data
            {
                xtype: 'panel',
                title: 'Customer Transaction',
                // layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                // defaults: {
                //   layout: 'vbox',
                //   flex: 1,
                //   bodyPadding: 10
                // },
                margin: "10 0 0 0",

                items: [
                    {
                      flex: 1,
                      xtype: 'myorderview',
                      enableFilter: true,
                      partnercode: PROJECTBASE,
                      toolbarItems: [
                        'detail', '|', 'filter', '|',
                        {
                            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                        },
                        {
                            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                        },
                        {
                            text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReportFromSearch',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                        },
                      ],
                      reference: 'myorder',
                      defaultPageSize: 10,
                      store: {
                            type: 'MyOrder', 
                            
                            proxy: {
                                type: 'ajax',
                                url: 'index.php',
                                // url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                            autoLoad: false, // Disable auto-loading of the store
                            
                        },

                        columns: [
       
                            { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
                            { text: 'Booking On', dataIndex: 'ordbookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
                           
                            { text: 'Customer Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130,
                                renderer: function (value, rec, rowrec) {
                                    // console.log(rec,rowrec,'rec')
                                    
                                    rec.style = 'color:#00008B'
                                
                                    return Ext.util.Format.htmlEncode(value)
                                }, 
                            },
                            { text: 'Gold Account No', dataIndex: 'achcode', filter: { type: 'string' }, minWidth: 130, hidden:true,
                                renderer: function (value, rec, rowrec) {
                                    // console.log(rec,rowrec,'rec')
                                    
                                    rec.style = 'color:#800000'
                                
                                    return Ext.util.Format.htmlEncode(value)
                                }, 
                            },
                            {
                                text: 'Bank Buy/Sell', dataIndex: 'ordtype',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['CompanySell', 'CompanySell'],
                                        ['CompanyBuy', 'CompanyBuy'],
                                        ['CompanyBuyBack', 'CompanyBuyBack'],
                                    ],
                                    renderer: function (value, rec) {
                                        if (value == 'CompanySell') return 'CompanySell';
                                        else if (value == 'CompanyBuy') return 'CompanyBuy';
                                        else return 'CompanyBuyBack';
                                    },
                                },
                    
                            },
                            { 
                                text: 'Xau Weight (g)', dataIndex: 'ordxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                } 
                            },
                            {
                                text: 'Original Price', dataIndex: 'ordprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            // {
                            //     text: 'P2 Price', dataIndex: 'ordbookingprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                            //     editor: {
                            //         xtype: 'numberfield',
                            //         decimalPrecision: 2
                            //     }
                            // },
                            // {
                            //     text: 'FP', dataIndex: 'ordfpprice',  hidden: true, exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                            //     editor: {    //field has been deprecated as of 4.0.5
                            //         xtype: 'numberfield',
                            //         decimalPrecision: 2
                            //     }
                            // },
                            {
                                text: 'Final Price', dataIndex: 'originalprice', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            {
                                text: 'Total Amount (RM)', dataIndex: 'ordamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            {
                                text: 'Commission Amount', dataIndex: 'commision', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            {
                                text: 'Discount', dataIndex: 'pricedifference', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                hidden: true,
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            {
                                text: 'Discount Info', dataIndex: 'discountAmount', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
                            },
                            {
                                text: 'Incoming/ Outgoing Payment (RM)', dataIndex: 'dbmpdtverifiedamount', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            {
                                text: 'Status', dataIndex: 'status', minWidth: 130,
                    
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Pending Payment'],
                                        ['1', 'Confirmed'],
                                        ['2', 'Paid'],
                                        ['3', 'Failed'],
                                        ['4', 'Reversed'],
                                        ['5', 'Pending Refund'],
                                        ['6','Refunded'],
                                        ['7','Pending Approval'],
                                        ['8','Rejected']
                                    ],
                    
                                },
                                renderer: function (value, rec) {
                                    if (value == '0') return 'Pending Payment';
                                    else if (value == '1') return 'Confirmed';
                                    else if (value == '2') return 'Paid';
                                    else if (value == '3') return 'Failed';
                                    else if (value == '4') return 'Reversed';
                                    else if (value == '5') return 'Pending Refund';
                                    else if (value == '6') return 'Refunded';
                                    else if (value == '7') return 'Pending Approval';
                                    else if (value == '8') return 'Rejected';
                                    else return 'Unspecified';
                                },
                            },
                            { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130,  },
                            {
                                text: 'Processing Fee (RM)', dataIndex: 'ordfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            { text: 'Transaction Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130,  renderer: 'boldText'  },
                            // { text: 'Gateway Ref No', dataIndex: 'dbmpdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Disbursement Ref No', dataIndex: 'dbmpdtreferenceno', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Partner', dataIndex: 'ordpartnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
                            { text: 'Bank Name', dataIndex: 'dbmbankname', hidden: true, filter: { type: 'string' }, minWidth: 130 },
                          
                            
                            //{ text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },
                           
                            { text: 'Order Buyer Id', dataIndex: 'ordbuyerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
                            { text: 'Order Cancel On', dataIndex: 'ordcancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
                            { text: 'Order Confirm On', dataIndex: 'ordconfirmon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true},
                            // { text: 'Order ID', dataIndex: 'orderid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
                            { text: 'Order No', dataIndex: 'ordorderno', filter: { type: 'string' }, minWidth: 130,
                                renderer: function (value, rec, rowrec) {
                                    // console.log(rec,rowrec,'rec')
                                    if (rowrec.data.ordtype == 'CompanySell'){
                                        rec.style = 'color:#209474'
                                    }
                                    if (rowrec.data.ordtype == 'CompanyBuy'){
                                        rec.style = 'color:#d07b32'
                                    }
                                    return Ext.util.Format.htmlEncode(value)
                                }, 
                            },
                          
                            /*{
                                text: 'Order Fee', dataIndex: 'ordfee', exportdecimal:2, filter: { type: 'string' }, align: 'right', hidden : true, minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }
                            },*/
                            { text: 'Product', dataIndex: 'ordproductname', hidden: true,  filter: { type: 'string' }, minWidth: 130 },
                           
                    
                            { text: 'Order Partner ID', dataIndex: 'ordpartnerid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
                            
                            { text: 'Order Remarks', dataIndex: 'ordremarks', filter: { type: 'string' }, minWidth: 130, hidden: true },
                            
                            
                        
                            /*
                            {
                                text: 'Original Amount (RM)', dataIndex: 'originalamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }
                            },*/
                            {
                                text: 'Payment Amount (RM)', hidden: true, dataIndex: 'pdtamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                            /*
                            {
                                text: 'Customer Fee (RM)', hidden: true, dataIndex: 'pdtcustomerfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }
                            },*/
                            { text: 'Payment Failed On', hidden: true, dataIndex: 'pdtfailedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            /* {
                                text: 'Payment Gateway Fee (RM)', dataIndex: 'pdtgatewayfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }
                            }, */
                          
                            { text: 'Transaction Date', dataIndex: 'dbmpdtrequestedon', hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                    
                            { text: 'Payment Location', hidden: true, dataIndex: 'pdtlocation', filter: { type: 'string' }, minWidth: 130 },
                            //{ text: 'Payment Merchant Ref No', dataIndex: 'pdtpaymentrefno', filter: { type: 'string' }, minWidth: 130 },
                          
                    
                            { text: 'Payment Refunded On', dataIndex: 'pdtrefundedon',  hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            //{ text: 'Payment Requested On', dataIndex: 'pdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                          
                            { text: 'Payment Signed Data', dataIndex: 'pdtsigneddata',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Payment Source Ref No', dataIndex: 'pdtsourcerefno', hidden: true, filter: { type: 'string' }, minWidth: 130 },
                            /*
                            {
                                text: 'Payment Status', dataIndex: 'pdtstatus',
                                filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Pending'],
                                        ['1', 'Success'],
                                        ['2', 'Pending Payment'],
                                        ['3', 'Cancelled'],
                                        ['4', 'Failed'],
                                        ['5', 'Refunded'],
                                    ],
                                    renderer: function (value, rec) {
                                        if (value == '0') return 'Pending';
                                        else if (value == '1') return 'Success';
                                        else if (value == '2') return 'Pending Payment';
                                        else if (value == '3') return 'Cancelled';
                                        else if (value == '4') return 'Failed';
                                        else if (value == '5') return 'Refunded';
                                        else return 'Unidentified';
                                    },
                                },
                    
                            },*/
                    
                            { text: 'Payment Success On', dataIndex: 'pdtsuccesson',  hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            { text: 'Payment Token', dataIndex: 'pdttoken', filter: { type: 'string' }, hidden: true, minWidth: 130 },
                            { text: 'Payment Transaction Date', dataIndex: 'pdttransactiondate', hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                            {
                                text: 'Disbursement Amount (RM)', dataIndex: 'dbmamount', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 2
                                }
                            },
                           
                          
                            // { text: 'Bank ID', dataIndex: 'dbmbankid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
                            { text: 'Bank Ref No', dataIndex: 'dbmbankrefno', hidden: true, filter: { type: 'string' }, minWidth: 130},
                            { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                            // { text: 'Account Name', dataIndex: 'dbmaccountname',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
                            // { text: 'Account No', dataIndex: 'dbmaccountnumber',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
                            // { text: 'Ace Bank Code', dataIndex: 'dbmacebankcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
                            /*{
                                text: 'Disbursement Fee (RM)', dataIndex: 'dbmfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                editor: {    //field has been deprecated as of 4.0.5
                                    xtype: 'numberfield',
                                    decimalPrecision: 3
                                }
                            },*/
                            // { text: 'Account Holder ID', dataIndex: 'dbmaccountholderid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
                            // { text: 'Merchant Ref No', dataIndex: 'dbmrefno', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Completed On', dataIndex: 'completedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 100, },
                            { text: 'Failed On', dataIndex: 'failedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true,  minWidth: 100, },
                            { text: 'Reversed On', dataIndex: 'reversedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true,  minWidth: 130 },
                            //{ text: 'Sales Person Code', dataIndex: 'salespersoncode', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130, hidden: true,  },
                            //{ text: 'Transaction Reference No', dataIndex: 'dbmtransactionrefno', filter: { type: 'string' }, minWidth: 130 },
                            //{ text: 'Requested On', dataIndex: 'dbmpdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
                            { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
                            { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
                            { text: 'Checker', dataIndex: 'checker', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130 },
                            { text: 'Action On', dataIndex: 'actionon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                        ],
                        
                    },
                  ]
      
            },

            {
                xtype: 'panel',
                title: 'Customer Redemption',
                // layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                // defaults: {
                //   layout: 'vbox',
                //   flex: 1,
                //   bodyPadding: 10
                // },
                margin: "10 0 0 0",

                items: [
                    {
                      flex: 1,
                      xtype: 'myconversionrequests',
                      enableFilter: true,
                      partnercode: PROJECTBASE,
                      toolbarItems: [
                        'detail', '|', 'filter', '|',
                        {
                            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                        },
                        {
                            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                        },
                        {
                            text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReportFromSearch',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                        },
                      ],
                      reference: 'myconversion',
                      defaultPageSize: 5,
                      store: {
                            type: 'MyConversion', 
                            
                            proxy: {
                                type: 'ajax',
                                url: 'index.php',
                                // url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                            autoLoad: false, // Disable auto-loading of the store
                            
                        },
                        
                    },
                  ]
      
            },
			{
                xtype: 'panel',
                title: 'Transfer Gold',
                collapsible: true,
                margin: "10 0 0 0",

                items: [
                    {
                      flex: 1,
                      xtype: 'mytransfergoldview_BSN',
                      enableFilter: true,
                      partnercode: PROJECTBASE,
                      toolbarItems: [
                        'detail', '|', 'filter', '|',
                        {
                            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                        },
                        {
                            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                        },
                        {
                            text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'transferreport', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                        },
                      ],
                      reference: 'mytransfergold',
                      defaultPageSize: 5,
                      store: {
                            type: 'MyTransferGold', 
                            
                            proxy: {
                                type: 'ajax',
                                url: 'index.php',
                                // url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                            autoLoad: false, // Disable auto-loading of the store
                            
                        },
                        
                    },
                  ]
      
            },
            // End test
            // set cust gold data
            {
                // title: 'Summary',
                region: 'south',
                height: 120,
                minHeight: 75,
                maxHeight: 800,
                layout: {
                    type: 'hbox',
                },
                margin: "10 0 0 0",
                defaults: {
                    bodyStyle: 'padding:0px;margin-top:10px',
                },
                cls: 'otc-main-center',
                // Size is 24 blocks spread across 3 screens
                items:[{   
                    // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'white',
                    //         display: 'inline-block',
                    //         color: '#000000',
                            
                    //     }
                    // },
                    // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 10,
                    margin: '0 10 0 0',
                    items: [{
                        title: 'MyGold 999.9',
                        header: {
                            style: 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        layout: 'hbox',
                        width: '100%',
                        items: [
                            {
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [
                                    // {
                                    //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    //     width: '100%',
                                    // },
                                    {
                                        xtype: 'displayfield',
                                        value: '0g',
                                        reference: "profile-goldbalance",
                                        bind: {
                                            value: '{profile-goldbalance}g'
                                        },
                                        width: '100%',
                                        fieldCls: 'otc-displayfield-gold',
                                    },

                                ],
                            
                            },
                            
                        ]
                    },]
                },
            
                {   
                    // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'white',
                    //         display: 'inline-block',
                    //         color: '#000000',
                            
                    //     }
                    // },
                    // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 10,
                    margin: '0 10 0 0',
                    hidden:true,
                    items: [{
                        title: 'Avg Purchase Price',
                        header: {
                            style: 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        layout: 'hbox',
                        width: '100%',
                        items: [
                            {
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [
                                    // {
                                    //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    //     width: '100%',
                                    // },
                                    {
                                        xtype: 'displayfield',
                                        value: 'RM0.00/g',
                                        bind: {
                                            value: 'RM{profile-avgbuyprice}/g'
                                        },
                                        reference: "profile-avgbuyprice",
                                        width: '100%',
                                    },

                                ],
                                
                            },
                            
                        ]
                    },]
                },
                {   
                    // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'white',
                    //         display: 'inline-block',
                    //         color: '#000000',
                            
                    //     }
                    // },
                    // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 10,
                    margin: '0 10 0 0',
                    hidden:true,
                    items: [{
                        title: 'Total Purchased',
                        header: {
                            style: 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        layout: 'hbox',
                        width: '100%',
                        items: [
                            {
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [
                                    // {
                                    //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    //     width: '100%',
                                    // },
                                    {
                                        xtype: 'displayfield',
                                        value: 'RM0.00',
                                        reference: "profile-totalcostgoldbalance",
                                        bind: {
                                            value: 'RM{profile-totalcostgoldbalance}'
                                        },
                                        width: '100%',
                                    },

                                ],
                            },
                            
                        ]
                    },]
                },
                {   
                    // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'white',
                    //         display: 'inline-block',
                    //         color: '#000000',
                            
                    //     }
                    // },
                    // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 10,
                    margin: '0 10 0 0',
                    hidden:true,
                    items: [{
                        title: 'Percentage',
                        header: {
                            style: 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        layout: 'hbox',
                        width: '100%',
                        items: [
                            {
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [
                                    // {
                                    //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    //     width: '100%',
                                    // },
                                    {
                                        xtype: 'displayfield',
                                        value: '0%',
                                        reference: "profile-diffcurrentpriceprcetage",
                                        bind: {
                                            value: '{profile-diffcurrentpriceprcetage}%'
                                        },
                                        width: '100%',
                                        fieldCls: 'otc-displayfield-red',
                                    },

                                ],
                            },
                            
                        ]
                    },]
                },
                {   
                    // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'white',
                    //         display: 'inline-block',
                    //         color: '#000000',
                            
                    //     }
                    // },
                    // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 10,
                    margin: '0 10 0 0',
                    items: [{
                        title: 'Current Gold Value',
                        header: {
                            style: 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        layout: 'hbox',
                        width: '100%',
                        items: [
                            {
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [
                                    // {
                                    //     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    //     width: '100%',
                                    // },
                                    {
                                        xtype: 'displayfield',
                                        value: 'RM0.00',
                                        reference: "profile-currentgoldvalue",
                                        bind: {
                                            value: 'RM{profile-currentgoldvalue}'
                                        },
                                        width: '100%',
                                        // labelCls: 'otc-displayfield-green',
                                        fieldCls: 'otc-displayfield-green',
                                    },

                                ],
                                
                                listeners : {
                                    render: function(p) {
                                        var theElem = p.getEl();
                                        withoutserialnumber = 0;
                                        // var theTip = Ext.create('Ext.tip.Tip', {
                                        //     //html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                        //     style: {

                                        //     },
                                        //     margin: '520 0 0 520',
                                        //     shadow: false,
                                        //     maxHeight: 400,
                                        // });
                                        
                                        p.getEl().on('mouseover', function(){
                                            theTip.showAt(theElem.getX(), theElem.getY());
                                        });
                                        
                                        p.getEl().on('mouseleave', function(){
                                            theTip.hide();
                                        });
                                    },
                                    click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberwithdo = new Ext.Window({
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 400,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                    {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'windowforserialnumberwithdo',
                                                                    }]
                                                        
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                            
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });
                                                
                                                
                                                if(vmv.get('withdoserialnumbers').length != 0){
                                                    windowforserialnumberwithdo.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('windowforserialnumberwithdo');

                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('withdoserialnumbers').map((x) => {
                                
                                                    panel.add(element.serialnoTemplateWithDO(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No records available for Serial Numbers with D/O ',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('windowforserialnumberwithdo').destroy();
                                                }
                                            
                                            
                                            }
                                        },
                                }
                            },
                            
                        ]
                    },]
                },
                ]

            },
            // Conversion container
            {
                title: 'Redemption',
                layout: 'hbox',
                // collapsible: true,
                cls: 'otc-main-center',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
				listeners: {
                        afterrender: function(form) {
                          var hasRedemption = snap.getApplication().hasPermission('/root/bsn/search/redemption');
                          settings = !hasRedemption; // reverse variable
                          // Update the hidden property based on the variable
                          form.setHidden(settings);
                        }
                    },
                items: [
                    {
                        defaultType: 'displayfield',
                        defaults: {
                          labelStyle: 'font-weight:bold',
                        },
                      
                        cls:'convert_slider',
                        flex: 6,
                        items:[
                            {
                                // xtype: 'announcementslider',
                                // xtype: 'panel',
                                // height: '70vh',
                                xtype: 'container',
                                // layout: "fit",
                                height: 480,
                                width: 400,
                               // width: Ext.getBody().getViewSize().width * 10/100,
                                reference: 'sliderhtml',
                                // html : function(){
                                //     return '<script src="./js/jquery-3.6.0.js"></script>'
                                // }()
                                
                            },
                        ]
                    },

                    {
                        xtype: 'container',
                        defaults: {
                            labelStyle: 'font-weight:bold',
                            align: 'center',
                        },
                        flex: 6,
                        cls:'convert_section',
                        items: [
                            {
                                xtype: 'form',
                                title: 'Quantity',
                                width: 700,
                                // collapsible: true,
                                // defaults: {
                                //     bodyPadding: 10
                                items: [
                                    {
                                        xtype: 'container',
                                        flex:1,
                                        width: '90%',
                                        layout:'hbox',
                                
                                        items: [
                                            {
                                                xtype: 'button',
                                                listeners: {
                                                    click: function() {
                                                        // Perform minus operation
                                                        var quantity = parseInt(elmnt.lookupReference('conversion-quantity').value);
                                                        goldbalance = (vm.get('profile-goldbalance') != null ? vm.get('profile-goldbalance') : 0.000);
                                                        weight = parseFloat(vm.get('convert.weight')).toFixed(3);

                                                        if(quantity > 1){
                                                            quantity--;
                                                            elmnt.lookupReference('conversion-quantity').setValue(quantity);
                                                            elmnt.lookupReference('totalconversionvalue').setValue((weight*quantity).toFixed(3));

                                                            totalconversionvalue = parseFloat(elmnt.lookupReference('totalconversionvalue').value).toFixed(3);
                                                            balanceafterconversion = goldbalance - totalconversionvalue;
                                                            if(balanceafterconversion >= parseFloat(vm.get('profile-minbalancexau'))){
                                                                balanceafterconversion = balanceafterconversion.toFixed(3)
                                                                elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion : 0.000);
                                                                elmnt.lookupReference('convertButton').setDisabled(false);
                                                            }else{
                                                                elmnt.lookupReference('convertButton').setDisabled(true);
                                                            }
                                                        }
                                                    }
                                                },
                                                flex: 1,
                                                baseCls: 'conversion-minus-button'
                                            },
                                            {
                                                xtype: 'textfield',
                                                reference: 'conversion-quantity',
                                                name: 'conversion-quantity',
                                                readOnly: true,
                                                value: 1,
                                                flex: 4,
                                                baseCls: 'conversion-text-box',
                                                maskRe: /[0-9.-]/,
                                                validator: function(v) {
                                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                                },
                                                // listeners: {
                                                //     change: function( fld, newValue, oldValue, opts ) {
                                                //         debugger;
                                                //         // Ext.getCmp('totalxauweightspotdashboard').disable();
                                                        
                                                //         // if(newValue == ''){
                                                //         //     Ext.getCmp('totalxauweightspotdashboard').enable();
                                                //         // }
                                                //     },                    
                                                // }
                                            },   
                                            {
                                                xtype: 'button',
                                                listeners: {
                                                    click: function() {
                                                        // Perform plus operation
                                                        var quantity = parseInt(elmnt.lookupReference('conversion-quantity').value) + 1;
                                                        weight = parseFloat(vm.get('convert.weight')).toFixed(3);
                                                        goldbalance = (vm.get('profile-goldbalance') != null ? vm.get('profile-goldbalance') : 0.000);

                                                        elmnt.lookupReference('conversion-quantity').setValue(quantity);
                                                        elmnt.lookupReference('totalconversionvalue').setValue((weight*quantity).toFixed(3));

                                                        totalconversionvalue = parseFloat(elmnt.lookupReference('totalconversionvalue').value).toFixed(3);
                                                        balanceafterconversion = goldbalance - totalconversionvalue;
                                                        balanceafterconversion = parseFloat(balanceafterconversion).toFixed(3);
                                                        elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion: 0.000);
                                                        if(balanceafterconversion >= parseFloat(vm.get('profile-minbalancexau'))){
                                                            elmnt.lookupReference('convertButton').setDisabled(false)
                                                        }else{
                                                            elmnt.lookupReference('convertButton').setDisabled(true)
                                                        }
                                                    }
                                                },
                                                width:'90%',
                                                style:'text-align:right',
                                                flex: 1,
                                                baseCls: 'conversion-plus-button'
                                            },
                                        ]
                                    },
                                    
                                ]    
                            },

                            // {
                            //     xtype: 'displayfield',
                            //     fieldLabel: 'Total Conversion',
                            //     name: 'totalconversion',
                            //     // value: "theData.information.fullname"
                            //     id: 'code', name:'code', reference: 'code', fieldLabel: 'Code',
                            // }, 
                            
                            
                            {
                              xtype: "container",
                              flex: 1,
                              width: 650,
                              //    cls:'conversion_details',
                              //layout:'hbox',
                              renderTo: Ext.getBody(),
                              layout: {
                                type: "hbox",
                                align: "stretch",
                              },
              
                              items: [
                                {
                                  flex: 1,
                                  xtype: "displayfield",
                                  value: "Total Redemption",
                                  enforceMaxLength: true,
                                  readOnly: true,
                                  fieldStyle: "font-size: 12px;text-align: left",
                                },
                                {
                                  flex: 1,
                                  xtype: "displayfield",
                                  name: "totalconversion",
                                  width:670,
                                  id: "totalconversionvalue",
                                  value: "1.000",
                                  reference: "totalconversionvalue",
                                  fieldStyle: "font-size: 12px;text-align: right",
                                  enforceMaxLength: true,
                                  readOnly: true,
                                },
                              ],
                            },
              

                            // {
                            //     xtype: 'draw',
                            //     width: 800,
                            //     height: 12,
                                
                            //     sprites: [{
                            //         type: 'line',
                            //         fromX: 0,
                            //         fromY: 10,
                            //         toX: 550,
                            //         toY: 10,
                            //         strokeStyle: '#D3D3D3',
                            //         lineWidth: 1
                            //     }]
                            // },

                            // {
                            //     xtype: 'container',
                            //     flex:1,
                            //     width: '85%',
                            //     cls:'conversion_details',
                            //     layout:'hbox',
                                
                            //     items: [
                            //         {
                            //             flex:1,
                            //             xtype: 'displayfield',
                            //             value : "Balance after Conversion",
                            //             cls: 'conversion_details_one',
                            //             //margin: '0 0 0 20',
                                          
                            //             enforceMaxLength: true,
                            //             readOnly : true,
                            //         },
                            //         {
                            //             flex:1,
                            //             xtype: 'displayfield',
                            //             name: 'balanceafterconversion',
                            //             value : "51.642",
                            //             cls: 'conversion_details_two',
                            //             //margin: '0 0 0 20',
                                          
                            //             enforceMaxLength: true,
                            //             readOnly : true,
                            //         },
                            //     ],
                            // },

                            {
                                xtype: 'draw',
                                width: 665,
                                height: 12,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 0,
                                    fromY: 10,
                                    toX: 670,
                                   // toX: Ext.getBody().getViewSize().width * 35/100, 
                                    toY: 10,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },

                            {
                              xtype: "container",
                              flex: 1,
                              width: 660,
                              renderTo: Ext.getBody(),
                              layout: {
                                type: "hbox",
                                align: "stretch",
                              },
              
                              items: [
                                {
                                  flex: 1,
                                  xtype: "displayfield",
                                  value: "Balance after Redemption",
                                  enforceMaxLength: true,
                                  readOnly: true,
                                  fieldStyle: "font-size: 12px;text-align: left",
                                },
                                {
                                  // flex:1,
                                  // xtype: 'displayfield',
                                  // name: 'balanceafterconversion',
                                  // value : "0.000",
                                  // reference: 'balanceafterconversion',
                                  // cls: 'conversion_details_two',
                                  // enforceMaxLength: true,
                                  // readOnly : true,
              
                                  flex: 1,
                                  xtype: "displayfield",
                                  name: "balanceafterconversion",
                                  width: 675,
                                  id: "balanceafterconversion",
                                  value: "0.0000",
                                  reference: "balanceafterconversion",
                                  fieldStyle: "font-size: 12px;text-align: right",
                                  enforceMaxLength: true,
                                  readOnly: true,
                                },
                              ],
                            },

                            // {
                            //     xtype: 'draw',
                            //     width: 800,
                            //     height: 12,
                                
                            //     sprites: [{
                            //         type: 'line',
                            //         fromX: 0,
                            //         fromY: 10,
                            //         toX: 550,
                            //         toY: 10,
                            //         strokeStyle: '#D3D3D3',
                            //         lineWidth: 1
                            //     }]
                            // },

                            // {
                            //     xtype: 'container',
                            //     flex:1,
                            //     width: '85%',
                            //     cls:'conversion_details',
                            //     layout:'hbox',
                                
                            //     items: [
                            //         {
                            //             flex:1,
                            //             xtype: 'displayfield',
                            //             value : "Conversion Fee",
                            //             cls: 'conversion_details_one',
                            //             //margin: '0 0 0 20',
                                          
                            //             enforceMaxLength: true,
                            //             readOnly : true,
                            //         },
                            //         {
                            //             flex:1,
                            //             xtype: 'displayfield',
                            //             name: 'conversionfee',
                            //             value : "51.00",
                            //             reference: 'conversionfee',
                            //             cls: 'conversion_details_two',
                            //             //margin: '0 0 0 20',
                                          
                            //             enforceMaxLength: true,
                            //             readOnly : true,
                            //         },
                            //     ],
                            // },

                            {
                                xtype: 'draw',
                                width: 675,
                                height: 12,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 0,
                                    fromY: 10,
                                    toX: 690,
                                   // toX: Ext.getBody().getViewSize().width * 35/100, 
                                    toY:10,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },
                            {
                                flex:1,
                                xtype:'button',
                                text:'Redeem',
                                reference: 'convertButton',
                                width:'90%',
                                style:'margin-top:55px',
                                handler:'conAqad',
                                // style: "background-color: gray;", // TEMP FUNCTION TO REMOVE
                            }

                        ],
                
                    },
                ]
      
            },
        ]
    },

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////

    // Buy pop up
    orderpopup: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 610,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main buypopout',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopup-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Current Gold Price RM - /g",
                        bind: {
                            value: 'Current Gold Price <b>{'+PROJECTBASE + '_CHANNEL.companyselldisplay}</b>', 
                        },
                        cls:'orderpopout_sub_title',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        height:240, 
                        cls:'orderpopout_box',
                        layout:'vbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Enter Amount *Min RM10",
                                cls: 'orderpopout_box_content_one orderpopout_box_content_one_text1',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                xtype: 'container',
                                flex:1,
                                width: '76%',
                                height:240, 
                                cls:'',
                                layout:'hbox',
                                
                                items: [
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : "RM",
                                        style: 'text-align:right;',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    },
                                    {
                                        flex:2,
                                        xtype: 'textfield',
                                        cls: 'orderpopout_box_content_one orderpopout_box_content_one_text2',
                                        value: '0.00',
                                        bind: {
                                            value: '{output.companysellamount}', 
                                        },
                                        name: 'companysellamount',
                                        reference: 'companysellamount',
                                        // bind: {
                                        //     value: '{'+ PROJECTBASE +'_CHANNEL.companysell}', 
                                        // },
                                        inputWrapCls: '',
                                        // remove default styling for div wrapping the input element and trigger button(s)
                                        triggerWrapCls: '',
                                        hideTrigger: true,
                                        decimalPrecision: 2,
                                        selectOnFocus: true,
                                        maskRe: new RegExp("[0-9.]+"),
                                        renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                        listeners: {
                                            change: function(combo, value) {
                                            
                                                // call formula 
                                                // AmounT / Price = Weight
                                                //debugger;
                                                weight = elmnt.calculateWeight(value, vm.data.BSN_CHANNEL.companysell );
                                        
                                                // Save 2 fields
                                                // 1) Save amount input
                                                // 2) Save calculated xau
                                                // 3) clear weight values
                                                //debugger;
                                                vm.set('input.companysellamount', parseFloat(value).toFixed(2));
                                                //this.setValue(parseFloat(value).toFixed(2));
                                                // debugger;
                                                //vm.getView('companysellamount').lookupReference('companysellamount').setValue(parseFloat(value).toFixed(2));
                                                //debugger;
                                                // vm.set('output.companysellxau', weight);
                                                // vm.set('input.companysellxau', 0);
                                            
                                                // vm.set('input.companysellxau', weight);

                                            },
                                            // Click listener
                                            render: function() {
                                                this.getEl().on('mousedown', function(e, t, eOpts) {
                                                    // Amount is in use 
                                                    vm.set('pricetoggle', 1);
                                                });
                                            }
                                
                                        }
                                        
                                    },
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : " ",
                                        cls: '',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    },
                                ]
                            
                            },
                            {
                                xtype: 'draw',
                                width: 800,
                                height: 50,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 20,
                                    fromY: 20,
                                    toX: 630,
                                    toY: 20,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },
                            {
                                xtype: 'container',
                                flex:1,
                                width: '90%',
                                height:240, 
                                cls:'',
                                layout:'hbox',
                                style:'padding-left:143px !important;',
                                
                                items: [
                                    {
                                        flex:1,
                                        xtype: 'textfield',
                                        cls: 'orderpopout_box_content_one orderpopout_box_content_one_text3',
                                        value: '0.000',
                                        bind: {
                                            value: '{output.companysellxau}', 
                                        },
                                        name: 'companysellxau',
                                        reference: 'companysellxau',
                                        inputWrapCls: '',
                                        // remove default styling for div wrapping the input element and trigger button(s)
                                        triggerWrapCls: '',
                                        hideTrigger: true,
                                        decimalPrecision: 3,
                                        selectOnFocus: true,
                                        maskRe: new RegExp("[0-9.]+"),
                                        renderer: Ext.util.Format.numberRenderer('0,000.000'),
                                        listeners: {
                                            change: function(combo, value) {
                                                // call formula 
                                                // AmounT = Weight + Price
                                                //debugger;
                                                amount = elmnt.calculateAmount(value, vm.data.BSN_CHANNEL.companysell);
                                        
                                                // Save 2 fields
                                                // 1) Save xau input
                                                // 2) Save amount output
                                                // 3) Clear amount values
                                                //debugger;
                                                vm.set('input.companysellxau', parseFloat(value).toFixed(3));
                                                //vm.getView('companysellxau').lookupReference('companysellxau').setValue(parseFloat(value).toFixed(3));
                                                //debugger;
                                                // vm.set('output.companysellamount', amount);
                                                // vm.set('input.companysellamount', 0);
                                                // vm.set('input.companysellxau', weight);

                                            

                                            },
                                            // Click listener
                                            render: function() {
                                                this.getEl().on('mousedown', function(e, t, eOpts) {
                                                    // Weight is in use 
                                                    vm.set('pricetoggle', 0);
                                                });
                                            }
                                        }
                                    },
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : "g",
                                        cls: '',
                                        width:'50%',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    }
                                ]
                            },
                            
                        ],
                    },
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopout_box_two',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Balance:",
                                cls: 'orderpopout_boxtwo_content_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "2500.00g",
                                reference: 'orderpopup-buy-goldbalance',
                                cls: 'orderpopout_boxtwo_content_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                    // Warning text
                    {
                        xtype: 'container',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Minimum buy amount cannot be lower than RM 10",
                        // bind: {
                        //     value: 'Minimum buy amount cannot be lower than RM<b>{profile-minbalancexau}</b>', 
                        // },
                        cls:'orderpopout_error_title',
                        //margin: '0 0 0 20',
                        reference: 'orderpopup-error-buy',
                        enforceMaxLength: true,
                        readOnly : true,
                        hidden: true,
                            
                    },
                    {
                        xtype: 'draw',
                        width: 800,
                        height: 50,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 20,
                            toX: 645,
                            toY: 20,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // Add sales and introducer code
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : getText('referralsalespersoncode')+'<br> <span style="font-weight:normal">(For WA, Teller, RO and PC ID Only) </span>',
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'referralsalespersoncode', 
                        reference: 'buyaqad-referralsalespersoncode',
                        cls: 'orderpopoutaqad_campaign',
                        maxLength: 5,
                        width: '90%', 
                        flex: 3, 

                    },
                    // {
                    //     style:'margin-top:15px',
                    //     flex:1,
                    //     xtype: 'displayfield',
                    //     value : getText('referralintroducercode'),
                    //     cls:'orderpopoutaqad_text_sell_subTitle',
                    //     //margin: '0 0 0 20',
                          
                    //     enforceMaxLength: true,
                    //     readOnly : true,
                            
                    // },

                    // { 
                    //     xtype: 'textfield',
                    //     name: 'referralintroducercode', 
                    //     reference: 'buyaqad-referralintroducercode',
                    //     cls: 'orderpopoutaqad_campaign',
                    //     maxLength: 5,
                    //     width: '90%', 
                    //     flex: 3, 

                    // },
                    // End sales and introducer

                    // {
                    //     flex:1,
                    //             xtype: 'panel',
                    //             viewModel: {},
                    //             height: 100,
                    //             width: 200,
                    //             items: {
                    //                 xtype: 'toggle',
                    //                 reference: 'toggleField'
                    //             },
                    //     //margin: '0 0 0 20',
                            
                    // },


                    // {
                    //     xtype: 'container',
                    //     items: [
                    //         { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'grnposlist', width: '90%', flex: 4, allowBlank: false, reference: 'grnposlist_field' },
                    //     ]
                    // },
                    
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
        formDialogButtons: [{
            xtype:'panel',
            flex:3
        },
        {
            text: 'Bank Sell',
            flex: 2,
            reference: 'orderpopup-button-buy',
            handler: function(btn) {
                if(vm.get('input.companysellamount') == 0){
                    Ext.MessageBox.show({
                        title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                        msg: 'The amount cannot be 0'
                    });
                }else{
                    myView = btn.up().up().parentView;
                    me = btn.up().up().classView;
                    me.buyAqad(btn.up().up(), myView); 
                }
                
                
            }
        },
        // {
        //     text: 'Close',
        //     flex: 1,
        //     handler: function(btn) {
        //         owningWindow = btn.up('window');
        //         owningWindow.close();
        //         me.gridFormView = null;
        //     }
        // },
        {
            xtype:'panel',
            flex: 2,
        }]
    },

    // Sell pop up
    orderpopupsell: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 610,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        // formViewModel: {},

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main sellpopout',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupsell-form",
                items: [
                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Current Gold Price RM - /g",
                        bind: {
                            value: 'Current Gold Price <b>{'+PROJECTBASE + '_CHANNEL.companybuydisplay}</b>', 
                        },
                        cls:'orderpopout_sub_title',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        height:240, 
                        cls:'orderpopout_box',
                        layout:'vbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Enter Amount *Min RM10",
                                cls: 'orderpopout_box_content_one orderpopout_box_content_one_text1',
                                //margin: '0 0 0 20',
                                width: '100%',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                xtype: 'container',
                                flex:1,
                                width: '76%',
                                height:240, 
                                cls:'',
                                layout:'hbox',
                                
                                items: [
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : "RM",
                                        style: 'text-align:right;',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    },
                                    {
                                        flex:2,
                                        xtype: 'textfield',
                                        cls: 'orderpopout_box_content_one orderpopout_box_content_one_text2',
                                        value: '0.00',
                                        bind: {
                                            value: '{output.companybuyamount}', 
                                        },
                                        name: 'companybuyamount',
                                        reference: 'companybuyamount',
                                        inputWrapCls: '',
                                        // remove default styling for div wrapping the input element and trigger button(s)
                                        triggerWrapCls: '',
                                        hideTrigger: true,
                                        decimalPrecision: 2,
                                        maskRe: new RegExp("[0-9.]+"),
                                        renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                        listeners: {
                                            change: function(combo, value) {
                                            
                                                // call formula 
                                                // AmounT / Price = Weight
                                                //debugger;
                                                weight = elmnt.calculateWeight(value, vm.data.BSN_CHANNEL.companybuy );
                                                
                                                // Save 2 fields
                                                // 1) Save amount input
                                                // 2) Save calculated xau
                                                // 3) clear weight values
                                                //debugger;
                                                vm.set('input.companybuyamount', value);
                                                // vm.set('output.companysellxau', weight);
                                                // vm.set('input.companysellxau', 0);
                                            
                                                // vm.set('input.companysellxau', weight);

                                            },
                                            // Click listener
                                            render: function() {
                                                this.getEl().on('mousedown', function(e, t, eOpts) {
                                                    // Amount is in use 
                                                    vm.set('pricetoggle', 1);
                                                });
                                            }
                                
                                        }
                                    },
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : " ",
                                        cls: '',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    },
                                ]
                            },
                            {
                                xtype: 'draw',
                                width: 800,
                                height: 50,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 20,
                                    fromY: 20,
                                    toX: 630,
                                    toY: 20,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },
                            {
                                xtype: 'container',
                                flex:1,
                                width: '90%',
                                height:240, 
                                cls:'',
                                layout:'hbox',
                                style:'padding-left:143px !important;',
                                
                                items: [
                                    {
                                        flex:1,
                                        xtype: 'textfield',
                                        cls: 'orderpopout_box_content_one orderpopout_box_content_one_text3',
                                        value: '0.000',
                                        bind: {
                                            value: '{output.companybuyxau}', 
                                        },
                                        name: 'companybuyxau',
                                        reference: 'companybuyxau',
                                        inputWrapCls: '',
                                        // remove default styling for div wrapping the input element and trigger button(s)
                                        triggerWrapCls: '',
                                        hideTrigger: true,
                                        decimalPrecision: 3,
                                        selectOnFocus: true,
                                        maskRe: new RegExp("[0-9.]+"),
                                        renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                        listeners: {
                                            change: function(combo, value) {
                                                // call formula 
                                                // AmounT = Weight + Price
                                    
                                                //debugger;
                                                amount = elmnt.calculateAmount(value, vm.data.BSN_CHANNEL.companybuy);
                                        
                                                // Save 2 fields
                                                // 1) Save xau input
                                                // 2) Save amount output
                                                // 3) Clear amount values
                                                //debugger;
                                                vm.set('input.companybuyxau', value);
                                                // vm.set('output.companysellamount', amount);
                                                // vm.set('input.companysellamount', 0);
                                                // vm.set('input.companysellxau', weight);

                                            

                                            },
                                            // Click listener
                                            render: function() {
                                                this.getEl().on('mousedown', function(e, t, eOpts) {
                                                    // Weight is in use 
                                                    vm.set('pricetoggle', 0);
                                                });
                                            }
                                        }
                                    },
                                    {
                                        flex:1,
                                        xtype: 'displayfield',
                                        value : "g",
                                        cls: '',
                                        width:'50%',
                                        //margin: '0 0 0 20',  
                                        enforceMaxLength: true,
                                        readOnly : true,
                                    }
                                ]
                            },
                            
                        ],
                    },
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopout_box_two_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Balance:",
                                cls: 'orderpopout_boxtwo_content_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "2500.00g",
                                reference: 'orderpopup-sell-goldbalance',
                                cls: 'orderpopout_boxtwo_content_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                    // Warning text
                    {
                        xtype: 'container',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Minimum sell amount cannot be lower than RM 10",
                        // bind: {
                        //     value: 'Minimum sell amount cannot be lower than RM<b>{profile-minbalancexau}</b>', 
                        // },
                        cls:'orderpopout_error_title',
                        //margin: '0 0 0 20',
                        reference: 'orderpopup-error-sell',
                        enforceMaxLength: true,
                        readOnly : true,
                        hidden: true,
                    },
                    {
                        xtype: 'draw',
                        width: 800,
                        height: 50,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 20,
                            toX: 645,
                            toY: 20,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     flex:1,
                    //             xtype: 'panel',
                    //             viewModel: {},
                    //             height: 100,
                    //             width: 200,
                    //             items: {
                    //                 xtype: 'toggle',
                    //                 reference: 'toggleField'
                    //             },
                    //     //margin: '0 0 0 20',
                            
                    // },


                    // {
                    //     xtype: 'container',
                    //     items: [
                    //         { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'grnposlist', width: '90%', flex: 4, allowBlank: false, reference: 'grnposlist_field' },
                    //     ]
                    // },
                    
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
        formDialogButtons: [{
            xtype:'panel',
            flex:3
        },
        {
            text: 'Bank Buy',
            flex: 2,
            reference: 'orderpopup-button-sell',
            handler: function(btn) {

                if(vm.get('input.companybuyamount') == 0){
                    Ext.MessageBox.show({
                        title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                        msg: 'The amount cannot be 0'
                    });
                }else{      
                    myView = btn.up().up().parentView;
                    me = btn.up().up().classView;
                    me.sellAqad(btn.up().up(), myView);
                }
            }
        },
        // {
        //     text: 'Close',
        //     flex: 1,
        //     handler: function(btn) {
        //         owningWindow = btn.up('window');
        //         owningWindow.close();
        //         me.gridFormView = null;
        //     }
        // },
        {
            xtype:'panel',
            flex: 2,
        }]
    },

    orderpopupsellaqad: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main sellpopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupsellaqad-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'sellaqad-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina Sell",
                                reference: 'sellaqad-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'sellaqad-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'sellaqad-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Sold",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'sellaqad-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sell Price",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200 / gram",
                                reference: 'sellaqad-price',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Buy",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'sellaqad-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'sellaqad-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM201.20",
                        reference: 'sellaqad-finaltotal',
                        cls:'orderpopoutaqad_text_sell_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Please complete payment within the next 3 minutes",
                        cls:'orderpopoutaqad_text_sell_four',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },
                  
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopout_box_two_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The rates offered will expire in",
                                cls: 'orderpopoutaqad_timmer_text',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "1:00",
                                reference: 'sellaqad-timer',
                                cls: 'orderpopoutaqad_timmer',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                    
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Campaign Code",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                        hidden:true
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'sellaqad-campaigncode',
                        cls: 'orderpopoutaqad_campaign',
                        width: '90%', 
                        flex: 3, 
                        hidden:true
                    },
                    
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'hiddenfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,

                    },

                    {
                        xtype: 'hiddenfield',
                        border: false,
                        layout: 'hbox',
                        style: {
                            marginLeft: 'auto',
                            marginRight: 'auto'
                        },
                        height:70,
                        flex: 1,
                        width: 400,
                        reference: 'sellaqad-securitypin',
                        cls: 'security_pin_panel',
                
                        items: [

                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                reference: 'init_pin_1',
                                inputType: 'password',
                                flex: 0.1,
                                width: 1,
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                        // debugger;
                                        this.lookupController().lookupReference('init_pin_2').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_2',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_3').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_3',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_4').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_4',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_5').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'), 
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_5',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_6').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_6',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.callParent(arguments);
                                    }
                                }
                            },
                            
                        ]
                    },
                    
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    orderpopupbuyaqad: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main buypopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupbuyaqad-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'buyaqad-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina",
                                reference: 'buyaqad-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'buyaqad-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'buyaqad-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Purchased:",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'buyaqad-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purchase Price",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200 / gram",
                                reference: 'buyaqad-price',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Buy",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'buyaqad-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        reference: 'buyaqad-discount-field',
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Discount Price RM/g",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM0.00",
                                reference: 'buyaqad-discount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        reference: 'buyaqad-discount-field-line',
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        reference: 'buyaqad-discountAmount-field',
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Discount Amount",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM0.00",
                                reference: 'buyaqad-discountAmount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        reference: 'buyaqad-discountAmount-field-line',
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'buyaqad-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM201.20",
                        reference: 'buyaqad-finaltotal',
                        cls:'orderpopoutaqad_text_buy_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Please complete payment within the next 3 minutes",
                        cls:'orderpopoutaqad_text_sell_four',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },
                  
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopout_box_two',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The rates offered will expire in",
                                cls: 'orderpopoutaqad_timmer_text',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "1:00",
                                reference: 'buyaqad-timer',
                                cls: 'orderpopoutaqad_timmer',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                    
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Campaign Code",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                        hidden:true    
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'buyaqad-campaigncode',
                        cls: 'orderpopoutaqad_campaign',
                        width: '90%', 
                        flex: 3, 
                        hidden:true
                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'hiddenfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'hiddenfield',
                        border: false,
                        layout: 'hbox',
                        style: {
                            marginLeft: 'auto',
                            marginRight: 'auto'
                        },
                        height:70,
                        flex: 1,
                        width: 400,
                        reference: 'buyaqad-securitypin',
                        cls: 'security_pin_panel',
                
                        items: [

                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                reference: 'init_pin_1',
                                inputType: 'password',
                                flex: 0.1,
                                width: 1,
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                        // debugger;
                                        this.lookupController().lookupReference('init_pin_2').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_2',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_3').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_3',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_4').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_4',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_5').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'), 
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_5',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_6').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_6',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.callParent(arguments);
                                    }
                                }
                            },
                            
                        ]
                    },
                    
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    orderpopupbuyReceipt: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main buypopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupbuyReceipt-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'buyReceipt-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina",
                                reference: 'buyReceipt-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'buyReceipt-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'buyReceipt-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Purchased:",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'buyReceipt-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purchase Price",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200 / gram",
                                reference: 'buyReceipt-price',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        reference: 'buyReceipt-discountprice-field',
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Discount Price RM/g",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM0.00",
                                reference: 'buyReceipt-discountprice',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        reference: 'buyReceipt-discountprice-field-line',
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        reference: 'buyReceipt-discountAmount-field',
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Discount Amount",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM0.00",
                                reference: 'buyReceipt-discountAmount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        reference: 'buyReceipt-discountAmount-field-line',
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Buy",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'buyReceipt-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'buyReceipt-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM201.20",
                        reference: 'buyReceipt-finaltotal',
                        cls:'orderpopoutaqad_text_buy_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Transaction",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Failed",
                                reference: 'buyReceipt-status',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    orderpopupsellReceipt: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main sellpopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupsellReceipt-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'sellReceipt-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina",
                                reference: 'sellReceipt-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'sellReceipt-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'sellReceipt-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Sold:",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'sellReceipt-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sell Price",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200 / gram",
                                reference: 'sellReceipt-price',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Buy",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'sellReceipt-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'sellReceipt-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM201.20",
                        reference: 'sellReceipt-finaltotal',
                        cls:'orderpopoutaqad_text_buy_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Transaction",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Failed",
                                reference: 'sellReceipt-status',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    orderpopupconvertaqad: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main convertpopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupconvertaqad-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'convertaqad-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina",
                                reference: 'convertaqad-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'convertaqad-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Contact Number",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "60135223809",
                                reference: 'convertaqad-contactno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'convertaqad-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Amount",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'convertaqad-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Making Charges",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertaqad-makingcharges',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Delivery Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertaqad-deliveryfee',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Redemption Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertaqad-fee',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },
                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Transaction Fee",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "RM200",
                    //             reference: 'convertaqad-transactionfee',
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Redemption Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'convertaqad-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'convertaqad-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM0.00",
                        reference: 'convertaqad-finaltotal',
                        cls:'orderpopoutaqad_text_convert_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Address 1",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'address1', 
                        reference: 'convertaqad-address1',
                        width: '90%', 
                        flex: 3, 
                        allowBlank: false,
                        blankText: 'This field is required',
                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Address 2",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'address2', 
                        reference: 'convertaqad-address2',
                        width: '90%', 
                        flex: 3, 

                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Postcode",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'postcode', 
                        reference: 'convertaqad-postcode',
                        width: '90%', 
                        flex: 3, 

                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "State",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'state', 
                        reference: 'convertaqad-state',
                        width: '90%', 
                        flex: 3, 

                    },
                  
                    
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Campaign Code",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                        hidden:true    
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'convertaqad-campaigncode',
                        width: '90%', 
                        flex: 3, 
                        hidden:true
                    },

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'hiddenfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'hiddenfield',
                        border: false,
                        layout: 'hbox',
                        style: {
                            marginLeft: 'auto',
                            marginRight: 'auto'
                        },
                        height:70,
                        flex: 1,
                        width: 400,
                        reference: 'convertaqad-securitypin',
                        cls: 'security_pin_panel',
                
                        items: [

                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                reference: 'init_pin_1',
                                inputType: 'password',
                                flex: 0.1,
                                width: 1,
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                        // debugger;
                                        this.lookupController().lookupReference('init_pin_2').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_2',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_3').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_3',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_4').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_4',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_5').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'), 
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_5',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_6').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_6',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.callParent(arguments);
                                    }
                                }
                            },
                            
                        ]
                    },
                    
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    orderpopupconvertReceipt: {
        controller: 'orderdashboard-orderdashboard',

        formDialogWidth: 700,
        formDialogHeight: 675,
        formDialogTitle: "Gold",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        scrollable:true,

        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
        
        defaults: {
            frame: false,
            //bodyPadding: 10
        },
        cls: 'otc-main convertpopout_aqad',
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "orderpopupconvertReceipt-form",
                items: [
                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Date",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Thu, 23 June 2022, 12:46:16",
                                reference: 'convertReceipt-date',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 655,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Name",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "John Cina",
                                reference: 'convertReceipt-fullname',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "NRIC",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "891010-10-8989",
                                reference: 'convertReceipt-mykadno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },


                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Contact No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0135223809",
                                reference: 'convertReceipt-contactno',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Gold Account No",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "EG72EC7BE1",
                                reference: 'convertReceipt-accountholdercode',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Amount",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "0.806 gram",
                                reference: 'convertReceipt-xau',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },


                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Making Charges",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertReceipt-makingcharges',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Delivery Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertReceipt-deliveryfee',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Redemption Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM200",
                                reference: 'convertReceipt-fee',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Total Redemption Fee",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM201.20",
                                reference: 'convertReceipt-amount',
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Purity",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "999.9 (LBMA Standard)",
                                cls: 'orderpopoutaqad_text_sell_two',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 12,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "Vault",
                    //             cls: 'orderpopoutaqad_text_sell_one',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //         {
                    //             flex:1,
                    //             xtype: 'displayfield',
                    //             value : "SG4S, Malaysia (Appointed Security Provider)",
                    //             cls: 'orderpopoutaqad_text_sell_two',
                    //             //margin: '0 0 0 20',
                                  
                    //             enforceMaxLength: true,
                    //             readOnly : true,
                    //         },
                    //     ],
                    // },

                    // {
                    //     xtype: 'draw',
                    //     width: 800,
                    //     height: 12,
                        
                    //     sprites: [{
                    //         type: 'line',
                    //         fromX: 5,
                    //         fromY: 10,
                    //         toX: 645,
                    //         toY: 10,
                    //         strokeStyle: '#D3D3D3',
                    //         lineWidth: 1
                    //     }]
                    // },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "The Staff",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Sammmu-123456789",
                                reference: 'convertReceipt-teller',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Delivery Address",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "",
                                reference: 'convertReceipt-address',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "Final Total",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        flex:1,
                        xtype: 'displayfield',
                        value : "RM201.20",
                        reference: 'convertReceipt-finaltotal',
                        cls:'orderpopoutaqad_text_buy_finalTotal',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'draw',
                        width: 800,
                        height: 40,
                        
                        sprites: [{
                            type: 'line',
                            fromX: 5,
                            fromY: 10,
                            toX: 645,
                            toY: 10,
                            strokeStyle: '#D3D3D3',
                            lineWidth: 1
                        }]
                    },

                    {
                        xtype: 'container',
                        flex:1,
                        width: '100%',
                        cls:'orderpopoutaqad_text_sell',
                        layout:'hbox',
                        
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Transaction",
                                cls: 'orderpopoutaqad_text_sell_one',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Failed",
                                reference: 'convertReceipt-status',
                                cls: 'orderpopoutaqad_text_sell_two',
                                style:'color:#009CBC',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                        ],
                    },
                ],
                // Input listeners here if any
            },
            {
                xtype: "form",
                flex: 0,
                width: 10,
                // reference: "grnposlist-form",
                items: [
                    
                ],
            }, //padding hbox
            //2nd hbox
        ],
    },

    // uploadmemberlistform: {
    //     controller: 'collection-poscollection',

    //     formDialogWidth: 700,
    //     formDialogHeight: 400,

    //     formDialogTitle: "Member List",

    //     // Settings
    //     enableFormDialogClosable: false,
    //     formPanelDefaults: {
    //         border: false,
    //         xtype: "panel",
    //         flex: 1,
    //         layout: "anchor",
    //         msgTarget: "side",
    //         margins: "0 0 10 10",
    //     },
    //     enableFormPanelFrame: false,
    //     formPanelLayout: "hbox",
    //     formViewModel: {},

    //     formPanelItems: [
    //         //1st hbox
    //         {
    //             xtype: "form",
    //             reference: "grnposlist-form",
    //             items: [
    //                 {
    //                     xtype: 'container',
    //                     layout: 'hbox',
    //                     items: [
                            
    //                         {
    //                             flex:1,
    //                             xtype: 'displayfield',
    //                             value : "<p>&#9679; Please verify and get approval before upload </p>",
    //                             margin: '0 0 0 20',
    //                             forceSelection: true,
    //                             enforceMaxLength: true,
    //                             readOnly : true,
    //                         },{
    //                             flex:1,
    //                             xtype: 'displayfield',
    //                             value : "<p>&#9679; Minimum 1 member record is required.</p>",
    //                             margin: '0 0 0 20',
    //                             forceSelection: true,
    //                             enforceMaxLength: true,
    //                             readOnly : true,
    //                         },
    //                         { xtype: 'panel', flex : 1},
    //                     ]
    //                 },
    //                 {
    //                     xtype: 'container',
    //                     layout: 'hbox',
    //                     items: [
    //                         { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'grnposlist', width: '90%', flex: 4, allowBlank: false, reference: 'grnposlist_field' },
    //                     ]
    //                 },
    //             ],
    //             // Input listeners here if any
    //         },
    //         {
    //             xtype: "panel",
    //             flex: 0,
    //             width: 10,
    //             items: [],
    //         }, //padding hbox
    //         //2nd hbox
    //     ],
    // },

});
