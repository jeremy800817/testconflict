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
        // ALRAJHI_CHANNEL: {
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



Ext.define('snap.view.orderdashboard.OrderDashboard_ALRAJHI',{
    extend: 'Ext.panel.Panel',
    xtype: 'orderdashboardview_ALRAJHI',

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
    listeners: {
        beforehide: function() {
            this.lookupReference('accountholdersearch').setValue('');
            this.lookupReference('profile-partnercusid').setValue('');

            this.lookupReference('profile-fullname').setValue('');
            this.lookupReference('profile-occupationcategory').setValue('');
            this.lookupReference('profile-mykadno').setValue('');
            this.lookupReference('profile-email').setValue('');
            this.lookupReference('profile-phoneno').setValue('');

            
            this.lookupReference('profile-address').setValue('');
            this.lookupReference('profile-status').setValue('');
            
            // this.lookupReference('profile-goldbalance').setValue('0g');
            // this.lookupReference('profile-avgbuyprice').setValue('RM0.00/g');
            // this.lookupReference('profile-totalcostgoldbalance').setValue('RM0.00');
            // this.lookupReference('profile-diffcurrentpriceprcetage').setValue('0%');
            // this.lookupReference('profile-currentgoldvalue').setValue('RM0.00');

        
            // Get transaction
            myaccountholdersearchresults = this.lookupReference('myaccountholdersearchresults');
            myaccountholdersearchresults.getStore().removeAll();
            // myaccountholdersearchresults.getStore().reload();

            // ordersearchgrid = this.lookupReference('myorder');
            // ordersearchgrid.getStore().removeAll();
            // // ordersearchgrid.getStore().reload();
            
        }
    },
    // for alrajhi biometric validation
    doBiometricValidation: function(button, functionName, type) {
        var panel = button.up('panel');
    
        button.setLoading(true); // show loading mask
        button.setDisabled(true);
    
        Ext.Ajax.request({
          url: 'http://127.0.0.1:5000/MyKAD/Scan',
          method: 'GET',
          timeout: 180000,
          buffer: 180000,
          params: {
            TrxID: 1234, // login id for teller
            Photo: true
          },
          success: function(response) {

            jsonText = response.responseText;
            const biometricsData  = JSON.parse(jsonText);
            // Create a form panel
        
            // End form panel
            if(biometricsData.validated === true) {
                // Do extra color

            }else{
                // Do extra color
            }
            let win = new Ext.Window ({
                title:'Display Identity Image',
                layout:'form',
                closeAction:'close',
                items: [
                    {
                        xtype: 'fieldcontainer',
                        layout: 'hbox',
                        width: 800,
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                layout: 'vbox',
                                flex: 1,
                                items: [
                                    {
                                        xtype: 'textfield',
                                        fieldLabel: 'Name',
                                        readOnly: true,
                                        value: biometricsData.name
                                    },
                                    {
                                        xtype: 'textfield',
                                        readOnly: true,
                                        fieldLabel: 'Identity No',
                                        value: biometricsData.kpt,
                                        reference: 'identityno'
                                    }
                                ]
                            },
                            {
                                xtype: 'fieldcontainer',
                                layout: 'vbox',
                                flex: 1,
                                items: [
                                    {
                                        xtype: 'textfield',
                                        readOnly: true,
                                        fieldLabel: 'Validated',
                                        fieldStyle: biometricsData.validated ? 'color: green;' : 'color: red;',
                                        value: biometricsData.validated ? 'Yes' : 'No',
                                    }
                                ]
                            },
                            // {
                            //     xtype: 'fieldcontainer',
                            //     layout: 'vbox',
                            //     flex: 1,
                            //     items: [
                            //         {
                            //             xtype: 'textfield',
                            //             readOnly: true,
                            //             hidden: true,
                            //             fieldLabel: 'Reason',
                            //             reference: 'biometricfailreason',
                            //             // fieldStyle: biometricsData.validated ? 'color: green;' : 'color: red;',
                            //             value: '',
                            //         }
                            //     ]
                            // }
                        ]
                    },
                    {
                        xtype: 'fieldcontainer',
                        layout: 'hbox',
                        width:800,
                        items:[
                            {
                                layout:'form',
                                flex:1,
                                style: 'text-align:center;',
                                items: [{
                                    layout: 'form',
                                    flex: 1,
                                    style: 'text-align: center;',
                                    items: [{
                                        width: 350,
                                        height: 200,
                                        xtype: 'image',
                                        src: 'data:image/png;base64,' + biometricsData.photo
                                    }]
                                }]
                            
                            },
                            // {
                            //     layout:'form',
                            //     flex:1,
                            //     style: 'text-align:center;',
                            //     items: [
                            //         {
                            //             width:350,
                            //             html: data.data.back_image,
                            //             style: 'text-align:center;',
                            //         },
                            //         {
                            //             xtype:'label',
                            //             text: 'Back Image'
                            //         }
                            //     ]
                            // }
                        ]
                    },
                    {
                        xtype: 'fieldcontainer',
                        layout: 'vbox',
                        flex: 1,
                        items: [
                            {
                                xtype: 'displayfield',
                                readOnly: true,
                                fieldLabel: 'Status',
                                fieldStyle: biometricsData.validated ? 'color: green;' : 'color: red;',
                                value: biometricsData.validated ? 'Validation succesful' : 'Validation failed, proceed with the registration?'
                            }
                        ]
                    },
                  // Add more fields for other data properties
                ],

                // Add buttons
                buttons: [{
                    text: 'Yes',
                    reference: 'button-biometrics-yes',
                    hidden: biometricsData.validated ? true : false,
                    handler: function (btn) {
                        elmnt.doBiometricSkip(button, functionName, biometricsData.kpt, type);
                    }
                }, {
                    text: 'No',
                    reference: 'button-biometrics-no',
                    hidden: biometricsData.validated ? true : false,
                    handler: function (btn) {
                        owningWindow = btn.up('window');
                        //owningWindow.closeAction='destroy';
                        owningWindow.close();
                    }
                }, {
                    text: 'Retry',
                    reference: 'button-biometrics-retry',
                    hidden: biometricsData.validated ? true : false,
                    handler: function (btn) {
                        owningWindow = btn.up('window');
                        //owningWindow.closeAction='destroy';
                        owningWindow.close();
                        elmnt.doBiometricValidation(btn);
                    }
                }],
            });
            
            win.show();
            
            button.setLoading(false); // remove loading mask
            button.setDisabled(false);
            // var data = Ext.JSON.decode(response.responseText);
            // Handle success
            // Trigger the function in the controller
             // Get a reference to the OtherController
            var orderDashboardController = elmnt.getController()

            if (orderDashboardController) {
                // Call the function in OtherController
                // Current Function name types
                // Buy, Sell, Convert
                orderDashboardController[functionName](elmnt);
            }
            
          },
          failure: function(response) {
            
            button.setLoading(false); // remove loading mask
            button.setDisabled(false);
            // Handle failure
            Ext.Msg.alert('Error', 'Unable to connect to Biometrics.');
            panel.down('#statusField').setValue('Error');
            panel.down('#statusField').addCls('error-msg');
          }
        });
    
        // display error message after 5 seconds
       
    },
    doBiometricSkip: function(button, functionName, identityno, type) {
        myView = elmnt;

        var remarkpage = Ext.create(myView.formClass, Ext.apply(myView.formOtcApproval ? myView.formOtcApproval : {}, {
            formDialogButtons: [{
                xtype: 'panel',
                flex: 1
            },
            {
                text: 'Submit',
                flex: 2.5,
                handler: function (modalBtn) {
                    var remarks = modalBtn.up().up().lookupReference('otcregisterremarks').getValue();
                    var identityNo = identityno;
                    console.log(remarks);
                    console.log(identityNo);
                    Ext.MessageBox.confirm(
                        'Confirm Approval', 'Are you sure you want to submit for approval ?', function (btn) {
                            if (btn === 'yes') {
                                // vm.set('otc-register-remarks', remarks);
                                snap.getApplication().sendRequest({
                                    hdl: 'otcregisterremarks', 'action': 'registerapproval', 'ic_no':identityNo, 'remarks': remarks, 'partnercode' : PROJECTBASE, 'type' : type,
                                }, 'Sending Approval').then(
                                    function (data) {
                                        console.log(data)
                                        if (data.success) {
                                            if (data.isawait) {
                                                Ext.MessageBox.wait('Waiting For Approval...', 'Please wait', {
                                                    icon: 'my-loading-icon'
                                                });
                                                const url = 'index.php?hdl=otcregisterremarks&action=checkapprovalstatus&id=' + data.id + '&approve=yes';
                                                const intervalId = setInterval(async () => {
                                                    try {
                                                        const response = await Ext.Ajax.request({
                                                            url: url,
                                                            method: 'GET'
                                                        });
                                                        const responseData = Ext.JSON.decode(response.responseText);
                                                        console.log(responseData)
                                            
                                                        if (!responseData.ispendingapproval) {
                                                            clearInterval(intervalId);
                                                            console.log('Approval process complete');
                                                            if (responseData.status === '1') {
                                                                // Code to execute when approval is approved

                                                                modalBtn.up().up().close();
                                                                Ext.MessageBox.show({
                                                                    title: type + 'Approved',
                                                                    buttons: Ext.MessageBox.OK,
                                                                    iconCls: 'x-fa fa-check-circle',
                                                                    msg: 'Proceed to ' + type + ' without biometric',
                                                                });

                                                                var orderDashboardController = elmnt.getController()
                                                                if (orderDashboardController) {
                                                                    orderDashboardController[functionName](elmnt);
                                                                }

                                                            } else {
                                                                // Code to execute when registration is not approved
                                                                modalBtn.up().up().close();

                                                                Ext.MessageBox.show({
                                                                    title: type + ' Not Approved',
                                                                    buttons: Ext.MessageBox.OK,
                                                                    iconCls: 'x-fa fa-times-circle',
                                                                    msg: 'Cannot Proceed to ' + type + ' without biometric',
                                                                });
                                                            }
                                                        }
                                                    } catch (error) {
                                                        console.error('Request failed', error);
                                                        clearInterval(intervalId);
                                                        Ext.MessageBox.show({
                                                            title: 'Error',
                                                            buttons: Ext.MessageBox.OK,
                                                            iconCls: 'x-fa fa-exclamation-circle',
                                                            msg: 'An error occurred while checking approval status. Please try again later.',
                                                        });
                                                    }
                                                }, 10000);
                                            } else {
                                                console.warn('Data is not awaiting approval.');
                                                Ext.MessageBox.show({
                                                    title: 'Not Await',
                                                    buttons: Ext.MessageBox.OK,
                                                    iconCls: 'x-fa fa-info-circle',
                                                    msg: 'The data is not awaiting approval.',
                                                });
                                            }
                                        } else {
                                            Ext.MessageBox.show({
                                                title: 'Error Message',
                                                msg: data.errorMessage,
                                                buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                            });
                                        }
                                });
                            }
                        }
                    );
                }
            },
            {
                xtype: 'panel',
                flex: 2,
            }, {
                text: 'Close',
                flex: 1,
                handler: function (btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        
        remarkpage.show();

    },
    // End validation

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
        //var websocketurl = 'wss://otc-uat.ace2u.com:8443/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.PosGold';
        var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+'INTLX.ALRAJHI';
        
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
        // return (Math.round( totalweight * 100 ) / 100).toFixed(3);
        return totalweight.toFixed(3);
    },

    // amount formula
    calculateAmount: function (weight, customerinput){
        var total = parseFloat(customerinput) * parseFloat(weight);
        //debugger;
        // Chrome workaround where .5 does not round up
        //debugger;
        // return (Math.round( total * 100 ) / 100).toFixed(2);
        return total.toFixed(2);
    },

    // Do Sell Validation
    validateSell: function (total, weight) {

        goldbalance = parseFloat( (vm.get('profile-goldbalance') ? vm.get('profile-goldbalance') : 0) );
        minbalancexau = parseFloat( (vm.get('profile-minbalancexau') ? vm.get('profile-minbalancexau') : 0) );

        // for test
        // goldbalance = parseFloat(4124.111);
        // minbalancexau = parseFloat( 0.02);

        threshold = 5.00;
    
        remainderGold = goldbalance - weight;
		    remainderGold = remainderGold.toFixed(3);
    
        // Check weight and total
        if (remainderGold >= minbalancexau){

            // Check if balance sufficient
   
            if (total >= threshold && weight <= goldbalance){
                // Enable button
                return true
            }
            else {
                text = 'Minimum sell amount cannot be lower than RM '+threshold;
                // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
                // Disable Button
                return false
            }

            

        }
        else {
            
            // Disable all
            // document.getElementById('btnpaybank').disabled= true;
            // document.getElementById("btnpaybank").style.opacity= .65;
            if(goldbalance < minbalancexau && goldbalance >= 0){
                text = 'The minimum sell value is '+minbalancexau+'g and you are required to have at least '+minbalancexau+'g in account';
                // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            }else if(remainderGold < minbalancexau){
                text = 'The minimum balance cannot lower than '+minbalancexau+'g in account';
                // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            }else{
                debugger;
                text = 'Minimum sell amount cannot be lower than RM '+threshold;
                // $('#minbalanceerror').html('<?php echo $lang['sell_minimumsell']; ?>');	
                vm.get('orderpopup-gridform-sell').lookupController().lookupReference('orderpopup-error-sell').setValue(text);
            }


            //debugger;
            return false
            // $('#minbalanceerror').show();
        }
    },

    validateBuy: function (total, weight) {

        goldbalance = parseFloat( (vm.get('profile-goldbalance') ? vm.get('profile-goldbalance') : 0) );
        minbalancexau = parseFloat( (vm.get('profile-minbalancexau') ? vm.get('profile-minbalancexau') : 0) );

        // for test
        // goldbalance = parseFloat(4124.111);
        // minbalancexau = parseFloat( 0.02);

        threshold = 5.00;
    
        // remainderGold = goldbalance - weight;
		// remainderGold = remainderGold.toFixed(3);
    
        // Check weight and total
        if (total >= threshold){
            // Enable button
            return true
        }
        else {
            // Disable Button
            return false
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

        var buyGoldPermission = snap.getApplication().hasPermission('/root/alrajhi/goldtransaction/buy');
        var sellGoldPermission = snap.getApplication().hasPermission('/root/alrajhi/goldtransaction/sell');

        // Ext.create('snap.store.OrderPriceStream');
        async function getList(){
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )
        var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + 'index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.ALRAJHI';
        //websocketurl = 'https://otc-uat.ace2u.com:8443/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.ALRAJHI';
        const source = new EventSource(websocketurl);
        // const source = new EventSource('https://otc-uat.ace2u.com:8443/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.ALRAJHI');
        // const source = new EventSource('https://10.10.55.114/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.PosGold');
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
                            emptyText: 'My Kad / Passport No',
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
                    // {   
                    //     // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                    //     // header: {
                    //     //     style: {
                    //     //         backgroundColor: 'white',
                    //     //         display: 'inline-block',
                    //     //         color: '#000000',
                                
                    //     //     }
                    //     // },
                    //     // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //     //title: 'Ask',
                      
                    //     flex:1,
                    //     xtype:'button',
                    //     text:'Scan',
                    //     glyph: 'thumbprint',
                    //     cls:'search_btn',
                    //     handler:'',
                    //     margin: "0 0 0 10",
                    //     handler: function(button) {
                    //         var panel = button.up('panel');
                        
                    //         button.setLoading(true); // show loading mask
                    //         button.setDisabled(true);
                        
                    //         Ext.Ajax.request({
                    //           url: 'http://127.0.0.1:5000/MyKAD/Scan',
                    //           method: 'GET',
                    //           timeout: 180000,
                    //           buffer: 180000,
                    //           params: {
                    //             TrxID: 1234,
                    //             Photo: true
                    //           },
                    //           success: function(response) {
                    
                    //             jsonText = response.responseText;
                    //             const biometricsData  = JSON.parse(jsonText);
                    //             // Create a form panel
                            
                    //             // End form panel
                    //             if(biometricsData.validated === true) {
                    //                 // Do extra color

                    //             }else{
                    //                 // Do extra color
                    //             }
                    //             let win = new Ext.Window ({
                    //                 title:'Display Identity Image',
                    //                 layout:'form',
                    //                 closeAction:'close',
                    //                 items: [
                    //                     {
                    //                         xtype: 'fieldcontainer',
                    //                         layout: 'hbox',
                    //                         width: 800,
                    //                         items: [
                    //                             {
                    //                                 xtype: 'fieldcontainer',
                    //                                 layout: 'vbox',
                    //                                 flex: 1,
                    //                                 items: [
                    //                                     {
                    //                                         xtype: 'textfield',
                    //                                         fieldLabel: 'Name',
                    //                                         readOnly: true,
                    //                                         value: biometricsData.name
                    //                                     },
                    //                                     {
                    //                                         xtype: 'textfield',
                    //                                         readOnly: true,
                    //                                         fieldLabel: 'Identity No',
                    //                                         value: biometricsData.kpt
                    //                                     }
                    //                                 ]
                    //                             },
                    //                             {
                    //                                 xtype: 'fieldcontainer',
                    //                                 layout: 'vbox',
                    //                                 flex: 1,
                    //                                 items: [
                    //                                     {
                    //                                         xtype: 'textfield',
                    //                                         readOnly: true,
                    //                                         fieldLabel: 'Validated',
                    //                                         value: biometricsData.validated
                    //                                     }
                    //                                 ]
                    //                             }
                    //                         ]
                    //                     },
                    //                     {
                    //                         xtype: 'fieldcontainer',
                    //                         layout: 'hbox',
                    //                         width:800,
                    //                         items:[
                    //                             {
                    //                                 layout:'form',
                    //                                 flex:1,
                    //                                 style: 'text-align:center;',
                    //                                 items: [{
                    //                                     layout: 'form',
                    //                                     flex: 1,
                    //                                     style: 'text-align: center;',
                    //                                     items: [{
                    //                                         width: 350,
                    //                                         height: 200,
                    //                                         xtype: 'image',
                    //                                         src: 'data:image/png;base64,' + biometricsData.photo
                    //                                     }]
                    //                                 }]
                                                
                    //                             },
                    //                             // {
                    //                             //     layout:'form',
                    //                             //     flex:1,
                    //                             //     style: 'text-align:center;',
                    //                             //     items: [
                    //                             //         {
                    //                             //             width:350,
                    //                             //             html: data.data.back_image,
                    //                             //             style: 'text-align:center;',
                    //                             //         },
                    //                             //         {
                    //                             //             xtype:'label',
                    //                             //             text: 'Back Image'
                    //                             //         }
                    //                             //     ]
                    //                             // }
                    //                         ]
                    //                     },
                    //                   // Add more fields for other data properties
                    //                 ]
                    //             });
                                
                    //             win.show();
                                
                    //             button.setLoading(false); // remove loading mask
                    //             button.setDisabled(false);
                    //             // var data = Ext.JSON.decode(response.responseText);
                    //             // Handle success
                    //             // Trigger the function in the controller
                    //              // Get a reference to the OtherController
                    //             var orderDashboardController = elmnt.getController()

                    //             if (orderDashboardController) {
                    //                 // Call the function in OtherController
                    //                 orderDashboardController.doSpotOrderBuyOTC(elmnt);
                    //             }
                                
                    //           },
                    //           failure: function(response) {
                                
                    //             button.setLoading(false); // remove loading mask
                    //             button.setDisabled(false);
                    //             // Handle failure
                    //             Ext.Msg.alert('Error', 'Unable to connect to Biometrics.');
                    //             panel.down('#statusField').setValue('Error');
                    //             panel.down('#statusField').addCls('error-msg');
                    //           }
                    //         });
                        
                    //         // display error message after 5 seconds
                           
                    //     },
                    // },
                    { 
                        flex:1,
                        xtype:'combobox',
                        cls:'combo_box',
                        store: {
                            fields: ['type', 'name'],
                            data : [
                                {"type":"1", "name":"CIC No (For Join Account Only)"},
                                {"type":"2", "name":"Identity Card No (For Individual Only)"},
                                {"type":"3", "name":"Company Registration No"},
                                //{"type":"4", "name":"Account No"},
                                
                            ]
                        },
                        listeners: {
                            select: function(combo, records, eOpts) {
                                accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                                newText = "Enter " + records.data.name + " here";
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
                        value: 1,
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
                        viewConfig : {
                            listeners : {
                                cellclick : function(view, cell, cellIndex, record,row, rowIndex, e) {
                              
                                    // Store information to Viewmodel
                                    vm.set('profile-fullname', record.data.fullname);
                                    vm.set('profile-accountholdercode', record.data.accountholdercode);
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
                                    // set conversion values
                                    balanceafterconversion = record.data.goldbalance - elmnt.lookupReference('totalconversionvalue').value;
                                    balanceafterconversion = parseFloat(balanceafterconversion).toFixed(3);
                                    elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion: 0.000);
                                    
                                    // Get data and populate profile details profiledetails
                                    var getDisplayController = this.up().up().up().up().getController();

                                    // If image is found (disabled for alrajhi)
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

                                    // Get transaction
                                    ordersearchgrid = getDisplayController.lookupReference('myorder');
                                    ordersearchgrid.getStore().proxy.url = 'index.php?hdl=myorder&action=getOtcOrders&mykadno='+record.data.mykadno+'&partnerid='+record.data.partnerid+'&accountholdercode='+record.data.accountholdercode;
                                    ordersearchgrid.getStore().reload();
                             
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
                    // {
                    //     xtype: 'fieldcontainer',
                    //     defaults: {
                    //       labelStyle: 'font-weight:bold',
                    //     },
                    //     layout: {
                    //         type: 'vbox',
                    //         align: 'center',
                    //         pack: 'center',
                    //     },
                    //     items:[
                    //         // image
                    //         // {
                    //         //     layout:'form',
                    //         //     flex:1,
                    //         //     style: 'text-align:center;',
                    //         //     items: [
                    //         //         {
                    //         //             xtype:'image',
                    //         //             src: 'src/resources/images/nric_template.jpg',
                    //         //             // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
                    //         //             region: 'south',
                    //         //             style: {
                    //         //                 'display': 'block',
                    //         //                 'margin': 'auto'
                    //         //             },
                                   
                    //         //             // width: 320,
                    //         //             // height: 240,
                    //         //             width: 400,
                    //         //             height: 300,
                    //         //             reference: "profile-front-image-default",
                    //         //         },
                    //         //         {
                    //         //             // xtype:'image',
                    //         //             // src: 'src/resources/images/nric_template.jpg',
                    //         //             // // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
                    //         //             // region: 'south',
                    //         //             // style: {
                    //         //             //     'display': 'block',
                    //         //             //     'margin': 'auto'
                    //         //             // },
                                   
                    //         //             // width: 320,
                    //         //             // height: 240,
                    //         //             width: 400,
                    //         //             height: 300,
                    //         //             reference: "profile-front-image",
                    //         //             hidden:true,
                    //         //         },
                    //         //         {
                    //         //             xtype:'label',
                    //         //             text: 'Front Image'  
                    //         //         }
                    //         //       ]
                    //         // },
                    //         // closed for alrajhi
                    //         // {
                    //         //     layout:'form',
                    //         //     flex:1,
                    //         //     style: 'text-align:center;',
                    //         //     items: [
                    //         //         {
                    //         //             // xtype:'image',
                    //         //             // src: 'src/resources/images/nric_template.jpg',
                    //         //             // // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
                    //         //             // region: 'south',
                    //         //             // style: {
                    //         //             //     'display': 'block',
                    //         //             //     'margin': 'auto'
                    //         //             // },
                                   
                    //         //             width: 320,
                    //         //             height: 240,
                    //         //             reference: "profile-back-image",
                    //         //         },
                    //         //         {
                    //         //             xtype:'label',
                    //         //             text: 'Back Image'
                    //         //         }
                    //         //     ]
                    //         // },
                           
                    //     ]
                    // },

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
                        fieldLabel: 'Account Number',
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
                    ]
                  },
                  
                //   {
                //     defaultType: 'displayfield',
                //     defaults: {
                //       labelStyle: 'font-weight:bold',
                //     },
                //     items: [
                     
                //         {
                //             xtype: "fieldset",
                //             title: "Account",
                //             collapsible: false,
                //             default: {
                //                 labelWidth: 30,
                //                 layout: "hbox",
                //             },
                //             items: [
                //                 {
                //                     xtype: "container",
                //                     width: Ext.getBody().getViewSize().width * 20/100,
                //                     height: 300,
                //                     id: 'widget',
                //                     scrollable: true,
                //                     reference:  "deliverystatusdisplayfield",
                //                     data: {
                //                         initialValue: [
                //                         { 'id': 123456789123, 'type': 'SAVINGS', 'amount': '12500', 'status': 'active'},
                //                         { 'id': 123456789124, 'type': 'CURRENT', 'amount': '12500', 'status': 'active'}
                //                     ],
                //                     },
                //                     tpl: `<div contenteditable="true">{initialValue.id}</div>`,
                //                     listeners: {
                //                         onRender : function(ct, position) {

                //                             // data = store.data.initialValue;
                //                             // data.forEach( (element) => {
                              
                //                         // debugger;
                //                             //     widget.update({name: 'Bell'});

                //                             //     tpl.append(Ext.getBody(), data);  
                //                             // });
                //                             // store.data.each(function(record) {
                //                             //     record.data.groupedNumbers = [];
                //                             //     for (var i = 0, j = 0; i < record.data.count; ++i, j = i % record.data.maxrows) {
                //                             //         record.data.groupedNumbers[j] = record.data.groupedNumbers[j] || { row: j, numbers: [] };
                //                             //         record.data.groupedNumbers[j].numbers.push(record.data.numbers[i]);
                //                             //     }
                //                             // });
                //                         }
                //                     }
                                
                //                 },
                //             ],
                //         },
                //     ]
                //   }
                ]
      
            },
            // Start salesman and introducer selector
            // {
            //     // title: 'Summary',
            //     height: 30,
            //     minHeight: 75,
            //     maxHeight: 800,
            //     layout: {
            //         type: 'hbox',
            //     },
            //     margin: "10 0 0 0",
            //     defaults: {
            //         bodyStyle: 'padding:0px;margin-top:10px',
            //     },
            //     cls: 'otc-main-center search_bar',
            //     // Size is 24 blocks spread across 3 screens
            //     items:[
            //         {   
            //             // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
            //             // header: {
            //             //     style: {
            //             //         backgroundColor: 'white',
            //             //         display: 'inline-block',
            //             //         color: '#000000',
                                
            //             //     }
            //             // },
            //             // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
            //             //title: 'Ask',
            //             flex: 4,
            //             margin: '0 10 0 0',
            //             items: [ {
            //                 xtype: 'textfield',
            //                 text: 'Introducer',
            //                 emptyText: 'Introducer Name',
            //                 flex:1,
            //                 style: 'text-align:center;',
            //                 width: '50%',
            //             //     listeners: {
            //             //         'change' : function(field, value, oldvalue, eOpts) {
            //             //             alert('a');
            //             //              this.store.load({params:{id: 1,search: value}});
            //             //         },
            //             //         onAfter : function(eventName, fn, scope, options) {
            //             //             alert('aa');
            //             //              this.store.load({params:{id: 1,search: value}});
            //             //         },
            //             //         scope:this,
            //             //    }
            //             }]
            //         },

            //         { 
            //             flex: 1,
            //             xtype:'combobox',
            //             cls:'combo_box',
            //             store: {
            //                 fields: ['abbr', 'name'],
            //                 data : [
            //                     {"abbr":"Select Salesman", "name":"Select Salesman"},
            //                     {"abbr":"Andy Low", "name":"Andy Low"},
            //                     {"abbr":"Jackie", "name":"Jackie"},
            //                     {"abbr":"Celia", "name":"Celia"}
                                
            //                 ]
            //             },
            //             queryMode: 'local',
            //             displayField: 'name',
            //             valueField: 'abbr',
            //             value: "Select Salesman",
            //             forceSelection: true,
            //             editable: false,
            //             margin: "0 10 0 10",
            //         },
            //         {
            //             xtype: 'panel',
            //             flex: 1,
            //         }
              
               
            //     ]

            // },
            // End selector
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
                    // header: false
                    // hidden: true,
                    header: false,
                    border: true,
                    // header: {
                    //     // Custom style for Migasit
                    //     /*style: {
                    //         backgroundColor: '#204A6D',
                    //     },*/
                    //     style : 'border-color: #204A6D;',
                    //     titlePosition: 0,
                    //     items: [{
                    //         xtype: 'button',
                    //         text: '-',
                    //         reference: 'spotorder-status',
                    //         id: 'spotorderonlinestatus',
                    //         //style: 'background-color: #B2C840'
                    //         style: 'border-radius: 20px;border-color: #204A6D',
                    //     }]
                    // },
                    autoHeight: true,
                    flex: 13,
                    padding : '0 5 0 0',
                    align: 'stretch',
                    listeners: {
                        afterrender: function(form) {
                          var hasSellGoldPermission = snap.getApplication().hasPermission('/root/alrajhi/goldtransaction/sell');
                          settings = !hasSellGoldPermission; // reverse variable
                          settings = false;
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
                            text: 'Sell Now',
                            handler: '',
                            //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                            // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                            // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                            flex: 4,
                            tooltip: 'Sell Gold',
                            reference: 'Sell Now',
                            // handler: 'doSpotOrderSellOTC',
                            handler: function(button) {
                                elmnt.doBiometricValidation(button, 'doSpotOrderSellOTC');
                            }
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
                          var hasBuyGoldPermission = snap.getApplication().hasPermission('/root/alrajhi/goldtransaction/buy');
                          settings = !hasBuyGoldPermission; // reverse variable
                          settings = false;
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
                                    text: 'Buy Now',
                                    handler: '',
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                    // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                    flex: 4,
                                    tooltip: 'Buy Gold',
                                    reference: 'Buy Now',
                                    handler: function(button) {
                                        elmnt.doBiometricValidation(button, 'doSpotOrderBuyOTC');
                                    }
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
                      store: {
                            type: 'MyOrder', proxy: {
                                type: 'ajax',
                                // url: 'index.php?hdl=myorder&action=list&partnercode='+PROJECTBASE,
                                url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
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
                                        var theTip = Ext.create('Ext.tip.Tip', {
                                            html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                            style: {

                                            },
                                            margin: '520 0 0 520',
                                            shadow: false,
                                            maxHeight: 400,
                                        });
                                        
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
                title: 'Conversion',
                layout: 'hbox',
                // collapsible: true,
                cls: 'otc-main-center',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
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
                                width: Ext.getBody().getViewSize().width * 15/100,
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
                                width: '90%',
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
                              width: "85%",
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
                                  value: "Total Conversion",
                                  enforceMaxLength: true,
                                  readOnly: true,
                                  fieldStyle: "font-size: 12px;text-align: left",
                                },
                                {
                                  flex: 1,
                                  xtype: "displayfield",
                                  name: "totalconversion",
                                  width: "85%",
                                  // id: "totalconversionvalue",
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
                                width: "100%",
                                height: 12,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 0,
                                    fromY: 10,
                                    //toX: 690,
                                    toX: Ext.getBody().getViewSize().width * 35/100, 
                                    toY: 10,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },

                            {
                              xtype: "container",
                              flex: 1,
                              width: "85%",
                              renderTo: Ext.getBody(),
                              layout: {
                                type: "hbox",
                                align: "stretch",
                              },
              
                              items: [
                                {
                                  flex: 1,
                                  xtype: "displayfield",
                                  value: "Balance after Conversion",
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
                                  width: "85%",
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
                                width: "100%",
                                height: 12,
                                
                                sprites: [{
                                    type: 'line',
                                    fromX: 0,
                                    fromY: 10,
                                    // toX: 690,
                                    toX: Ext.getBody().getViewSize().width * 35/100, 
                                    toY:10,
                                    strokeStyle: '#D3D3D3',
                                    lineWidth: 1
                                }]
                            },
                            {
                                flex:1,
                                xtype:'button',
                                text:'Convert',
                                reference: 'convertButton',
                                width:'90%',
                                style:'margin-top:55px',
                                // handler:'conAqad',
                                handler: function(button) {
                                    elmnt.doBiometricValidation(button, 'conAqad');
                                }
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
                                value : "Enter Amount *Min RM5",
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
                                                weight = elmnt.calculateWeight(value, vm.data.ALRAJHI_CHANNEL.companysell );
                                        
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
                                                amount = elmnt.calculateAmount(value, vm.data.ALRAJHI_CHANNEL.companysell);
                                        
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
                                value : "Gold Purchased:",
                                cls: 'orderpopout_boxtwo_content_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM2500.00",
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
                        value : "Minimum buy amount cannot be lower than RM 5",
                        bind: {
                            value: 'Minimum buy amount cannot be lower than RM<b>{profile-minbalancexau}</b>', 
                        },
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
            text: 'Buy Gold',
            flex: 2,
            reference: 'orderpopup-button-buy',
            handler: function(btn) {
                myView = btn.up().up().parentView;
                me = btn.up().up().classView;
                me.buyAqad(btn.up().up(), myView);
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
                                value : "Enter Amount *Min RM5",
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
                                                weight = elmnt.calculateWeight(value, vm.data.ALRAJHI_CHANNEL.companybuy );
                                        
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
                                                amount = elmnt.calculateAmount(value, vm.data.ALRAJHI_CHANNEL.companybuy);
                                        
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
                                value : "Gold Purchased:",
                                cls: 'orderpopout_boxtwo_content_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "RM2500.00",
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
                        value : "Minimum sell amount cannot be lower than RM 5",
                        bind: {
                            value: 'Minimum sell amount cannot be lower than RM<b>{profile-minbalancexau}</b>', 
                        },
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
            text: 'Sell Gold',
            flex: 2,
            reference: 'orderpopup-button-sell',
            handler: function(btn) {
                myView = btn.up().up().parentView;
                me = btn.up().up().classView;
                me.sellAqad(btn.up().up(), myView);
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
                                value : "Bank Acc. No",
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'sellaqad-campaigncode',
                        cls: 'orderpopoutaqad_campaign',
                        width: '90%', 
                        flex: 3, 

                    },

                    // Add combobox to select accounts
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Select Account",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype:'combobox',
                        name: 'accountselection', 
                        reference: 'sellaqad-accountselection',
                        cls: 'orderpopoutaqad_campaign',
                        // cls:'combo_box',
                        width: '90%', 
                        flex: 3, 
                        style:'margin-top:15px',
                        // cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                        enforceMaxLength: false,
                        readOnly : false,
                        flex:1,
              
                        store: {
                            fields: ['combination', 'combination'],
                            // data : [
                            //     {"accno":"3192301412", "name":"Joint Account"},
                            
                            // ]
                        },
                        // tpl: [
                        //     '<ul class="x-list-plain">',
                        //     '<tpl for=".">',
                        //     // '<li class="',
                        //     // Ext.baseCSSPrefix, 'grid-group-hd ',
                        //     // Ext.baseCSSPrefix, 'grid-group-title">{accno}</li>',
                        //     '<li class="x-boundlist-item">',
                        //     '{accountnumber}',
                        //     '</li>',
                        //     '</tpl>',
                        //     '</ul>'
                        // ],
                        listeners: {
                            // select: function(combo, records, eOpts) {
                            //     accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                            //     newText = "Enter " + records.data.name + " here"
                            //     accountholdersearch.setEmptyText(newText);
                            // }
                        },
                        queryMode: 'local',
                        displayField: 'combination',
                        valueField: 'combination',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                        // listeners: {
                        //     select: {
                        //         fn: 'showRegistrationForm'
                        //     }
                        // }    

                    },
                    // End account Selector

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'form',
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
                                value : "Bank Acc. No",
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
                                value : "Gold Purchase",
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'buyaqad-campaigncode',
                        cls: 'orderpopoutaqad_campaign',
                        width: '90%', 
                        flex: 3, 

                    },

                    // Add combobox to select accounts
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Select Account",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype:'combobox',
                        name: 'accountselection', 
                        reference: 'buyaqad-accountselection',
                        cls: 'orderpopoutaqad_campaign',
                        // cls:'combo_box',
                        width: '90%', 
                        flex: 3, 
                        style:'margin-top:15px',
                        // cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                        enforceMaxLength: false,
                        readOnly : false,
                        flex:1,
              
                        store: {
                            fields: ['combination', 'combination'],
                            // data : [
                            //     {"accno":"3192301412", "name":"Joint Account"},
                            
                            // ]
                        },
                        // tpl: [
                        //     '<ul class="x-list-plain">',
                        //     '<tpl for=".">',
                        //     // '<li class="',
                        //     // Ext.baseCSSPrefix, 'grid-group-hd ',
                        //     // Ext.baseCSSPrefix, 'grid-group-title">{accno}</li>',
                        //     '<li class="x-boundlist-item">',
                        //     '{accountnumber}',
                        //     '</li>',
                        //     '</tpl>',
                        //     '</ul>'
                        // ],
                        listeners: {
                            // select: function(combo, records, eOpts) {
                            //     accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                            //     newText = "Enter " + records.data.name + " here"
                            //     accountholdersearch.setEmptyText(newText);
                            // }
                        },
                        queryMode: 'local',
                        displayField: 'combination',
                        valueField: 'combination',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                        // listeners: {
                        //     select: {
                        //         fn: 'showRegistrationForm'
                        //     }
                        // }    

                    },
                    // End account Selector

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'form',
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
                                value : "Bank Acc. No",
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
                                value : "Gold Purchase",
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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
                                value : "Bank Acc. No",
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
                                value : "Gold Purchase",
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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
                    // ADD CONTACT
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
                    // END CONTACT
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
                                value : "Bank Acc. No",
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
                                value : "Quantity",
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
                    // NEW STUFF
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
                    // END NEW STUFF
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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

                    // Address
                    // {
                    //     xtype: 'container',
                    //     flex:1,
                    //     width: '100%',
                    //     cls:'orderpopoutaqad_text_sell',
                    //     layout:'hbox',
                        
                    //     items: [
                    //       {
                    //           flex:1,
                    //           xtype: 'displayfield',
                    //           value : "Address",
                    //           cls: 'orderpopoutaqad_text_sell_one',
                    //           //margin: '0 0 0 20',
                                
                    //           enforceMaxLength: true,
                    //           readOnly : true,
                    //       },
                    //       {
                    //           flex:1,
                    //           xtype: 'displayfield',
                    //           value : "-",
                    //           reference: 'convertaqad-address',
                    //           cls: 'orderpopoutaqad_text_sell_two',
                    //           //margin: '0 0 0 20',
                                
                    //           enforceMaxLength: true,
                    //           readOnly : true,
                    //       },
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
                    // End address
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
                    // New user field
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
                    // End new user field
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Campaign Code",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype: 'textfield',
                        name: 'campaigncode', 
                        reference: 'convertaqad-campaigncode',
                        width: '90%', 
                        flex: 3, 

                    },

                    // Add combobox to select accounts
                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Select Account",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                        
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    { 
                        xtype:'combobox',
                        name: 'accountselection', 
                        reference: 'convertaqad-accountselection',
                        cls: 'orderpopoutaqad_campaign',
                        // cls:'combo_box',
                        width: '90%', 
                        flex: 3, 
                        style:'margin-top:15px',
                        // cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                        enforceMaxLength: false,
                        readOnly : false,
                        flex:1,
            
                        store: {
                            fields: ['combination', 'combination'],
                            // data : [
                            //     {"accno":"3192301412", "name":"Joint Account"},
                            
                            // ]
                        },
                        // tpl: [
                        //     '<ul class="x-list-plain">',
                        //     '<tpl for=".">',
                        //     // '<li class="',
                        //     // Ext.baseCSSPrefix, 'grid-group-hd ',
                        //     // Ext.baseCSSPrefix, 'grid-group-title">{accno}</li>',
                        //     '<li class="x-boundlist-item">',
                        //     '{accountnumber}',
                        //     '</li>',
                        //     '</tpl>',
                        //     '</ul>'
                        // ],
                        listeners: {
                            // select: function(combo, records, eOpts) {
                            //     accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                            //     newText = "Enter " + records.data.name + " here"
                            //     accountholdersearch.setEmptyText(newText);
                            // }
                        },
                        queryMode: 'local',
                        displayField: 'combination',
                        valueField: 'combination',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                        // listeners: {
                        //     select: {
                        //         fn: 'showRegistrationForm'
                        //     }
                        // }    

                    },
                    // End account Selector

                    {
                        style:'margin-top:15px',
                        flex:1,
                        xtype: 'displayfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        //margin: '0 0 0 20',
                          
                        enforceMaxLength: true,
                        readOnly : true,
                            
                    },

                    {
                        xtype: 'form',
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
                                value : "Vault",
                                cls: 'orderpopoutaqad_text_sell_one',
                                //margin: '0 0 0 20',
                                  
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "SG4S, Malaysia (Appointed Security Provider)",
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
    formOtcApproval: {
        formDialogWidth: 950,
        controller: 'otcregister-otcregister',

        formDialogTitle: 'Approve Registration',

        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 10 10'
        },
        enableFormPanelFrame: false,
        formPanelLayout: 'hbox',
        formViewModel: {

        },

        formPanelItems: [

            {
                items: [

                    {
                        xtype: 'fieldset', title: 'Remarks', collapsible: false,
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                layout: {
                                    type: 'hbox',
                                },
                                items: [
                                    {
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', reference: 'otcregisterremarks'
                                    },
                                ]
                            },
                        ]
                    }
                ],
            },
        ],

    },



});
