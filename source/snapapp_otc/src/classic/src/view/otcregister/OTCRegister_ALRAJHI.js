// Init searchfield
Ext.define('snap.view.otcregister.OTCRegister_ALRAJHI',{
    extend: 'Ext.panel.Panel',
    xtype: 'otcregisterview_ALRAJHI',

    requires: [

        // 'Ext.layout.container.Fit',
        'snap.view.otcregister.OTCRegisterController',
        // 'snap.view.orderdashboard.OrderDashboardModel',
        // 'snap.store.OrderPriceStream',


    ],
    store: {
        occupationcategory: Ext.create('snap.store.OccupationCategory'),
        occupationsubcategorychecker: Ext.create('snap.store.OccupationSubCategory'),
        occupationsubcategory: Ext.create('snap.store.OccupationSubCategory'),
        bankaccounts: Ext.create('snap.store.BankAccounts')
    },
    formClass: 'snap.view.gridpanel.GridFormOtc',
    controller: 'otcregister-otcregister',
    viewModel: {
        data: {
            name: "Register",
            fees: [],
            permissions : [],
            status: '',

        }
    },
    listeners: {
        beforehide: function() {
        
            this.lookupReference('otcregisterform').setHidden(true);
            searchbar = this.lookupReference('otcregisterform-searchbar');
            if(searchbar){
                searchbar.setHidden(true);
            }
            this.lookupReference('casasearchfields').reset();
            this.lookupReference('otcregisterform-biometrics').setHidden(false);

            var orderDashboardController = this.getController()

            if (orderDashboardController) {
                // Call the function in OtherController
                // Current Function name types
                // Buy, Sell, Convert
                orderDashboardController['_clearRegistrationForm'];
            }
            
        }
    },
    doBiometricValidation: function(button, functionName = null) {
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
            // if(biometricsData.validated === true) {
            //     // Do extra color

            // }else{
            //     // Do extra color
            // }
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
                        elmnt.doBiometricSkip(button, biometricsData.kpt);
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
            // debugger;
            if (orderDashboardController) {
                // Call the function in OtherController
                // Current Function name types
                // Buy, Sell, Convert
                if(functionName){
                    orderDashboardController[functionName](elmnt);
                }else{
                    // proceed to next aka registration if validation true
                    if(biometricsData.validated === true) {
                        elmnt.lookupReference('otcregisterform-biometrics').setHidden(true);
                        elmnt.lookupReference('otcregisterform-searchbar').setHidden(false);
        
                        elmnt.moveSelectionToSearchBox();
                    }else{
                       // 'biometricfailreason'
                       // 'Fingerprint is a required procedure'

                    }
                }     
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
    // Do multiple
    doMultipleBiometricValidation: function(button, count = 2) {
        
        var loopCount = 0;

        function performValidation() {
          if (loopCount < count) {
            Ext.Ajax.request({
                // Ajax request configuration options
                url: 'http://127.0.0.1:5000/MyKAD/Scan',
                method: 'GET',
                timeout: 180000,
                buffer: 180000,
                params: {
                    TrxID: 1234, // login id for teller
                    Photo: true
                },
                success: function(response) {
                    // Handle success
        
                    jsonText = response.responseText;
                    const biometricsData = JSON.parse(jsonText);
        
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
                            handler: function(btn) {
                                elmnt.doBiometricSkip(button, biometricsData.kpt);
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
                    
                    if (biometricsData.validated === true) {
                        // Validated, continue to the next iteration
                        loopCount++;
                        performValidation();
                    } else {
                        // Not validated, end the loop
                        handleLoopCompletion(false);
                    }
                },
                failure: function(response) {
                    // Handle failure
                    handleLoopCompletion(false);
                }
                });
          } else {
            // Loop completed all iterations
            handleLoopCompletion(true);
          }
        }
      
        function handleLoopCompletion(validationResult) {
            // Code to execute after the loop completes
            button.setLoading(false);
            button.setDisabled(false);

            if(validationResult == true){
                elmnt.lookupReference('otcregisterform-biometrics').setHidden(true);
                elmnt.lookupReference('otcregisterform-searchbar').setHidden(false);
                
                elmnt.moveSelectionToSearchBox();
            }
         
        }
      
        // Start the loop
        performValidation();
        
    },
    // Skip biometrics
    doBiometricSkip: function(button, identityno) {
        // proceed to next aka registration
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
                                    hdl: 'otcregisterremarks', 'action': 'registerapproval', 'ic_no':identityNo, 'remarks': remarks, 'partnercode' : PROJECTBASE, 'type' : 'Registration',
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
                                                                // Code to execute when registration is approved

                                                                elmnt.lookupReference('statusremarks').setValue(remarks);
                                                                modalBtn.up().up().close();
                                                                elmnt.lookupReference('otcregisterform-biometrics').setHidden(true);
                                                                elmnt.lookupReference('otcregisterform-searchbar').setHidden(false);
                                                                elmnt.moveSelectionToSearchBox();
    
                                                                Ext.MessageBox.show({
                                                                    title: 'Registration Approved',
                                                                    buttons: Ext.MessageBox.OK,
                                                                    iconCls: 'x-fa fa-check-circle',
                                                                    msg: 'Proceed to Registration without biometric',
                                                                });
                                                            } else {
                                                                // Code to execute when registration is not approved
                                                                elmnt.lookupReference('statusremarks').setValue('');
                                                                elmnt.lookupReference('otcregisterform-biometrics').setHidden(false);
                                                                elmnt.lookupReference('otcregisterform-searchbar').setHidden(true);
                                                                Ext.MessageBox.show({
                                                                    title: 'Registration Not Approved',
                                                                    buttons: Ext.MessageBox.OK,
                                                                    iconCls: 'x-fa fa-times-circle',
                                                                    msg: 'Cannot Proceed to Registration without biometric',
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
                                
                                // elmnt.lookupReference('statusremarks').setValue(remarks);
                                // modalBtn.up().up().close();
                                // elmnt.lookupReference('otcregisterform-biometrics').setHidden(true);
                                // elmnt.lookupReference('otcregisterform-searchbar').setHidden(false);
                                // elmnt.moveSelectionToSearchBox();

                            }
                        }
                    );
                }
            },
            // {
            //     text: 'Reject',
            //     flex: 2.5,
            //     handler: function (modalBtn) {
            //         var remarks = modalBtn.up().up().lookupReference('otcregisterremarks').getValue();
            //         Ext.MessageBox.confirm(
            //             'Confirm Rejection', 'Are you sure you want to reject?', function (btn) {
            //                 if (btn === 'yes') {
            //                     modalBtn.up().up().close();
            //                     elmnt.lookupReference('otcregisterform-biometrics').setHidden(false);
            //                     elmnt.lookupReference('otcregisterform-searchbar').setHidden(true);
            //                 }

            //             });
            //     }
            // },
            // {
            //     text: 'Reject',
            //     flex: 2.5,
            //     handler: function (modalBtn) {
            //         var sm = myView.getSelectionModel();
            //         var selectedRecords = sm.getSelection();
            //         var remarks = Ext.getCmp('pepremarks').getValue();
            //         Ext.MessageBox.confirm(
            //             'Confirm', 'Are you sure you want to reject ?', function (btn) {
            //                 if (btn === 'yes') {
            //                     snap.getApplication().sendRequest({
            //                         hdl: 'mypepsearchresult', 'action': 'rejectAccountHolder', id: selectedRecords[0].data.id, 'remarks': remarks
            //                     }, 'Sending request....').then(
            //                         function (data) {
            //                             if (data.success) {
            //                                 myView.getSelectionModel().deselectAll();
            //                                 myView.getStore().reload();

            //                                 owningWindow = modalBtn.up('window');
            //                                 owningWindow.close();
            //                                 me.gridFormView = null;

            //                             } else {
            //                                 Ext.MessageBox.show({
            //                                     title: 'Error Message',
            //                                     msg: data.errorMessage,
            //                                     buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
            //                                 });
            //                             }
            //                         });
            //                 }
            //             });
            //     }
            // },
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
    moveSelectionToSearchBox: function(){

        // carry over values from
        selectionValue = elmnt.lookupReference('accounttype-register').getValue();

        switch (selectionValue) {
            case '21':
              // Handle case for type 21 (Company)
              // Your code here
              elmnt.lookupReference('casasearchtype').setValue(3);
              break;
          
            case '22':
              // Handle case for type 22 (Co Heading)
              // Your code here
              elmnt.lookupReference('casasearchtype').setValue(1);
              break;
          
            case '23':
              // Handle case for type 23 (Sole Proprietorship)
              // Your code here
              elmnt.lookupReference('casasearchtype').setValue(2);
              break;
          
            case '24':
              // Handle case for type 24 (Individual)
              // Your code here
              // Do Single validation check 
              elmnt.lookupReference('casasearchtype').setValue(2);
              break;
          
            default:
              // Handle default case if the type does not match any of the defined cases
              // Your code here
              break;
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

        this.callParent(arguments);
    },
    permissionRoot: '/root/gtp/cust',
    //store: { type: 'Order' },
    store: 'orderPriceStream',	
    // formDialogWidth: 950,
    layout: 'fit',
    // width: 500,
    // height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    //bodyPadding: 25,

    items: {
        profiles: {
            classic: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            neptune: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            graphite: {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            },
            'classic-material': {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            }
        },
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
            // Add alrajhi biometric and prompt for remark
            {
                // title: 'Summary',
                height: 175,
                minHeight: 75,
                maxHeight: 800,
                layout: {
                    type: 'vbox',
                    align: 'center',
                    pack: 'center'
                },
                margin: "10 0 0 0",
                defaults: {
                    bodyStyle: 'padding:0px;margin-top:10px',
                },
                cls: 'otc-main-center',
                reference: 'otcregisterform-biometrics',
                // Size is 24 blocks spread across 3 screens
                items: [
                    {
                        xtype: 'container',
                        layout: 'vbox',
                        items: [
                            {
                                xtype: 'displayfield',
                                value: 'Select an option below to continue',
                                fieldStyle: 'font-weight: bold;'
                            },
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'vbox',
                        height: 75,
                        items: [
                            { 
                                xtype:'combobox',
                                cls:'combo_box',
                                store: {
                                    fields: ['type', 'name'],
                                    data : [
                                        {"type":"24", "name":"Individual"},
                                        {"type":"21", "name":"Company"},
                                        {"type":"22", "name":"Co Heading"},
                                        {"type":"23", "name":"Sole Proprietorship"},
                            
                                        //{"type":"4", "name":"Account No"},
                                        
                                    ]
                                },
                                // listeners: {
                                //     select: function(combo, records, eOpts) {
                                //         // set condition
                                //         accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                                //         newText = "Enter " + records.data.name + " here";
                                //         accountholdersearch.setEmptyText(newText);
                                //         // this.up().up().up().getController().lookupReference('casasearchtype').setValue(records.data.type);
                                //     }
                                // },
                                reference: 'accounttype-register',
                                queryMode: 'local',
                                displayField: 'name',
                                valueField: 'type',
                                forceSelection: true,
                                editable: false,
                                margin: "0 10 0 10",
                                value: '24' // Set the default value to "Individual" (type: "24")
                            },
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'button',
                                text: 'Verify Biometrics',
                                margin: '0 10 0 0',
                                handler: function(button) {
                                    // check above type and do custom validation
                                    selectionValue = button.up().up().up().up().lookupReference('accounttype-register').getValue();
                                    // Legend for selectionvalue 
                                  
                                    // {"type":"21", "name":"Company"},
                                    // {"type":"22", "name":"Co Heading"},
                                    // {"type":"23", "name":"Sole Proprietorship"},
                                    // {"type":"24", "name":"Individual"},
                                    switch (selectionValue) {
                                        case '21':
                                          // Handle case for type 21 (Company)
                                          // Your code here
                                          elmnt.doMultipleBiometricValidation(button);
                                          break;
                                      
                                        case '22':
                                          // Handle case for type 22 (Co Heading)
                                          // Your code here
                                          elmnt.doMultipleBiometricValidation(button);
                                          break;
                                      
                                        case '23':
                                          // Handle case for type 23 (Sole Proprietorship)
                                          // Your code here
                                          elmnt.doBiometricValidation(button);
                                          break;
                                      
                                        case '24':
                                          // Handle case for type 24 (Individual)
                                          // Your code here
                                          // Do Single validation check 
                                          elmnt.doBiometricValidation(button);
                                          break;
                                      
                                        default:
                                          // Handle default case if the type does not match any of the defined cases
                                          // Your code here
                                          break;
                                      }
                                }
                            },
                            {
                                xtype: 'button',
                                text: 'Biometric Unavailable',
                                handler: function(button) {
                                    elmnt.doBiometricSkip(button);
                                }
                            }
                        ]
                    }
                ]

            },
            // Add search bar 
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
                reference: 'otcregisterform-searchbar',
                hidden: true,
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
                            emptyText: 'Select search type from dropdown',
                            flex:1,
                            style: 'text-align:center;',
                            width: '90%',
                            reference: 'casasearchfields',
                       
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

                    { 
                        flex:1,
                        xtype:'combobox',
                        cls:'combo_box',
                        store: {
                            fields: ['type', 'name'],
                            data : [
                                {"type":"1", "name":"CIC No"},
                                {"type":"2", "name":"Identity Card No"},
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
                        handler: 'getAccountsFromCasa'
                    },
              
               
                ]

            },
            // Return all account types in dropdown form
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
                hidden: true,
                reference: 'casaaccountlist-tab',
                cls: 'otc-main-center search_bar',
                // Size is 24 blocks spread across 3 screens
                items:[
                    {   
                        
                        title: 'Accounts:',
                        flex:2,
                        margin: "15 0 0 0",
                    },
                    { 
                        flex:1,
                        xtype:'combobox',
                        cls:'combo_box',
                        store: {
                            fields: ['accountnumber', 'accounttypestr'],
                            // data : [
                            //     {"accno":"3192301412", "name":"Joint Account"},
                            
                                
                            // ]
                        },
                        tpl: [
                            '<ul class="x-list-plain">',
                            '<tpl for=".">',
                            // '<li class="',
                            // Ext.baseCSSPrefix, 'grid-group-hd ',
                            // Ext.baseCSSPrefix, 'grid-group-title">{accno}</li>',
                            '<li class="x-boundlist-item">',
                            '<span class="fa fa-circle x-color-{accountstatuscolor}"></span> ',
                            '{accountnumber} - {accounttypestr}',
                            '</li>',
                            '</tpl>',
                            '</ul>'
                        ],
                        listeners: {
                            // select: function(combo, records, eOpts) {
                            //     accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                            //     newText = "Enter " + records.data.name + " here"
                            //     accountholdersearch.setEmptyText(newText);
                            // }
                        },
                        reference: 'casaaccountlist',
                        queryMode: 'local',
                        displayField: 'accountnumber',
                        valueField: 'accno',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                        listeners: {
                            select: {
                                fn: 'showRegistrationForm'
                            }
                        }
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
                        // xtype:'button',
                        // text:'SEARCH',
                        // iconCls: 'x-fa fa-search',
                        // cls:'search_btn',
                        // handler:'',
                        // margin: "0 0 0 10",
                        // handler: 'showRegistrationForm'
                    },
              
               
                ]

            },
            // Hidden for future use
            // {
            //     xtype: 'panel',
            //     title: getText('nricupload'),
            //     layout: 'hbox',
            //     collapsible: true,
            //     cls: 'otcpanel',
            //     defaults: {
            //       layout: 'vbox',
            //       flex: 1,
            //       bodyPadding: 10
            //     },
            //     margin: "10 0 0 0",
            //     items: [
            //         {
            //             xtype: 'panel',
            //             defaults: {
            //               labelStyle: 'font-weight:bold',
            //             },
            //             layout: {
            //                 type: 'vbox',
            //                 align: 'center',
            //                 pack: 'center'
            //             },
            //             flex:1, 
            //             items: [
            //                     {
            //                     xtype:'image',
            //                     src: 'src/resources/images/nric-front.png',
            //                     // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
            //                     region: 'south',
            //                     style: {
            //                         'display': 'block',
            //                         'margin': 'auto'
            //                     },
                           
            //                     width: 282,
            //                     height: 166,
            //                 },
            //                 { xtype: 'filefield',fieldLabel: getText('uploadnricfront')+' <span style="color:red;">*</span>', name: 'uploadnricfront', margin: "10 0 0 0", width: '90%', flex: 4, allowBlank: false, reference: 'uploadnricfront',
            //                     listeners:{
            //                         afterrender:function(cmp){
            //                         cmp.fileInputEl.set({
            //                             accept:'image/*' // or w/e type
            //                         });
            //                         }
            //                     }
            //                 },
            //             ]
            //         },
               
            //       {
            //         xtype: 'panel',
            //         defaults: {
            //           labelStyle: 'font-weight:bold',
            //         },
            //         flex:1, 
            //         layout: {
            //             type: 'vbox',
            //             align: 'center',
            //             pack: 'center'
            //         },
            //         items: [
            //             {
            //                 xtype:'image',
            //                 src: 'src/resources/images/nric-back.png',
            //                 // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
            //                 region: 'south',
            //                 style: {
            //                     'display': 'block',
            //                     'margin': 'auto'
            //                 },
                       
            //                 width: 282,
            //                 height: 166,
            //             },
            //             { xtype: 'filefield',fieldLabel: getText('uploadnricback')+' <span style="color:red;">*</span>', name: 'uploadnricback', margin: "10 0 0 0", width: '90%', flex: 4, allowBlank: false, reference: 'uploadnricback',
            //                 listeners:{
            //                     afterrender:function(cmp){
            //                     cmp.fileInputEl.set({
            //                         accept:'image/*' // or w/e type
            //                     });
            //                     }
            //                 }
            //             },
            //         ]
            //       }
            //     ]
      
            // },
            // Register
            {
                xtype: 'form',
                reference: 'otcregisterform',
                hidden: true,
                title: getText('register'),
                // layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                items:[ 
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-join",
                    title: 'Link Information',
                    items: [
                        {
                           
                            items: [
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    items: [
                                        //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                        // { xtype: 'hidden', hidden: true, reference: 'state', name: 'state' },
                                        { xtype: 'displayfield', flex: 3, reference: 'heading', fieldLabel: 'Heading', name: 'heading', width: '90%', labelWidth: 150, allowBlank: true},
                                        //{ xtype: 'displayfield', flex: 3, reference: 'nationality', fieldLabel: getText('nationality')+' <span style="color:red;">*</span>', name: 'nationality', width: '90%', labelWidth: 150, margin: '0 10 0 50', allowBlank: false,},
                                    ]
                                },
                                // { xtype: 'displayfield', reference: 'fullname', fieldLabel: 'Primary ' + getText('fullname')+' <span style="color:red;">*</span>', name: 'fullname', width: '90%', labelWidth: 150, allowBlank: false},
                            ]
                        },
                    ]
                },
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-personal",
                    title: getText('personalinformation'),
                    items: [
                        {
                           
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                { xtype: 'hidden', hidden: true, reference: 'partnercusid', name: 'partnercusid' },
                                { xtype: 'hidden', hidden: true, reference: 'branchident', name: 'branchident' },
                                { xtype: 'hidden', hidden: true, reference: 'partnerdata', name: 'partnerdata' },
                                //{ xtype: 'displayfield', reference: 'evidencecode', fieldLabel: 'Evidence Code', name: 'evidencecode', width: '90%', labelWidth: 150},
                               
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                //         // { xtype: 'hidden', hidden: true, reference: 'state', name: 'state' },
                                       
                                //         // { xtype: 'displayfield', flex: 3, reference: 'gender', fieldLabel: getText('gender')+' <span style="color:red;">*</span>', name: 'gender', width: '90%', labelWidth: 150, margin: '0 10 0 50', allowBlank: false,},
                                //     ]
                                // },
                            
                                { xtype: 'displayfield', flex: 3.3, reference: 'fullname', fieldLabel: 'Primary ' + getText('fullname')+' <span style="color:red;">*</span>', name: 'fullname', width: '90%', labelWidth: 150, allowBlank: false},
                                { xtype: 'displayfield', flex: 3.3, reference: 'mykadno', fieldLabel: 'Primary ' + getText('nric')+' <span style="color:red;">*</span>', name: 'nric', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 12,
                                    maxLength     : 12,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                { xtype: 'textfield', flex: 3.3, reference: 'mobile', fieldLabel: getText('mobile')+' <span style="color:red;">*</span>', name: 'mobile', width: '90%', labelWidth: 150, allowBlank: false,
                                minLength     : 10,
                                maxLength     : 15,
                                enforceMinLength : true,
                                enforceMaxLength : true,
                                maskRe: /[0-9-]/,
                                validator: function(v) {
                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                },

                            },
                             { xtype: 'textfield', flex: 3.3, reference: 'email', fieldLabel: getText('email')+' <span style="color:red;">*</span>', name: 'email', width: '90%', labelWidth: 150, allowBlank: false, vtype: 'email'},
                             { xtype: 'displayfield', flex: 3.3, reference: 'partyid', fieldLabel: 'Party ID', name: 'partyid', width: '90%', labelWidth: 150, allowBlank: true, vtype: 'partyid'},

                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                //         { xtype: 'hidden', hidden: true, reference: 'state', name: 'state' },
                                //         { xtype: 'displayfield', flex: 3, reference: 'address', fieldLabel: getText('address')+' <span style="color:red;">*</span>', name: 'address', width: '90%', labelWidth: 150, allowBlank: true},
                                //         { xtype: 'displayfield', flex: 3, reference: 'bumiputera', fieldLabel: getText('bumiputera')+' <span style="color:red;">*</span>', name: 'bumiputera', width: '90%', labelWidth: 150, margin: '0 10 0 50', allowBlank: true,},
                                //     ]
                                // },
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         // { xtype: 'textfield', flex: 0.3, reference: 'city', fieldLabel: getText('city')+' <span style="color:red;">*</span>', name: 'city', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                //         { xtype: 'textfield', flex: 3, reference: 'postcode', fieldLabel: getText('postcode')+' <span style="color:red;">*</span>', name: 'postcode', width: '90%', labelWidth: 150, allowBlank: true,
                                //             minLength     : 5,
                                //             maxLength     : 5,
                                //             enforceMinLength : true,
                                //             enforceMaxLength : true,
                                //         },
                                //         { xtype: 'displayfield', flex: 3, reference: 'race', fieldLabel: getText('race')+' <span style="color:red;">*</span>', name: 'race', width: '90%', labelWidth: 150, margin: '0 10 0 50', allowBlank: true,},
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                //     ]
                                // },
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                //         { xtype: 'textfield', flex: 3, reference: 'city', fieldLabel: getText('city')+' <span style="color:red;">*</span>', name: 'city', width: '90%', labelWidth: 150, allowBlank: false,},
                                //         { xtype: 'displayfield', flex: 3, reference: 'religion', fieldLabel: getText('religion')+' <span style="color:red;">*</span>', name: 'religion', width: '90%', labelWidth: 150, margin: '0 10 0 50', allowBlank: false,},
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                        
                                        
                                //     ]
                                // },
                                // { xtype: 'displayfield', reference: 'fullname', fieldLabel: 'Primary ' + getText('fullname')+' <span style="color:red;">*</span>', name: 'fullname', width: '90%', labelWidth: 150, allowBlank: false},
                                
                              
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         { xtype: 'hidden', hidden: true, reference: 'city', name: 'city' },
                                //         { xtype: 'hidden', hidden: true, reference: 'state', name: 'state' },
                                //         // { xtype: 'textfield', flex: 0.3, reference: 'city', fieldLabel: getText('city')+' <span style="color:red;">*</span>', name: 'city', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                //         { xtype: 'displayfield', flex: 0.3, reference: 'postcode', fieldLabel: getText('postcode')+' <span style="color:red;">*</span>', name: 'postcode', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false,
                                //             minLength     : 5,
                                //             maxLength     : 5,
                                //             enforceMinLength : true,
                                //             enforceMaxLength : true,
                                //         },
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                //         { xtype: 'panel', flex: 0.1, width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                //     ]
                                // },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                // Custom NOK ALRAJHI for secondary accounts
                
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-nok",
                    //title: getText('nokinformation'),
                    title: 'Secondary Information',
                    items: [
                        {
                           
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                // { xtype: 'textfield', reference: 'nokfullname', fieldLabel: getText('fullname')+' <span style="color:red;">*</span>', name: 'nokfullname', width: '90%', labelWidth: 150, allowBlank: false},
                                
                                { xtype: 'displayfield', flex: 3.3, reference: 'nokfullname', fieldLabel: 'Secondary ' + getText('fullname')+' <span style="color:red;">*</span>', name: 'nokfullname', width: '90%', labelWidth: 150, allowBlank: true},
                                { xtype: 'displayfield', flex: 3.3,reference: 'nokmykadno', fieldLabel: 'Secondary ' + getText('nric')+' <span style="color:red;">*</span>', name: 'noknric', width: '90%', labelWidth: 150, allowBlank: true,
                                    minLength     : 12,
                                    maxLength     : 12,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                //  {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [

                                { xtype: 'textfield', flex: 3.3, reference: 'nokphoneno', fieldLabel: getText('mobile')+' <span style="color:red;">*</span>', name: 'nokmobile', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 10,
                                    maxLength     : 15,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9.-]/,
                                    validator: function(v) {
                                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                        },

                                },
                                { xtype: 'textfield', flex: 3.3, reference: 'nokemail', fieldLabel: getText('email')+' <span style="color:red;">*</span>', name: 'nokemail', width: '90%', labelWidth: 150, allowBlank: false},
                                
                                //     ]
                                // },
                                { xtype: 'displayfield', flex: 3.3, reference: 'nokpartyid', fieldLabel: 'Party ID', name: 'nokpartyid', width: '90%', labelWidth: 150, allowBlank: true, vtype: 'nokpartyid'},

                                { xtype: 'displayfield', flex: 3.3, reference: 'nokrelationship', fieldLabel: getText('relationship')+' <span style="color:red;">*</span>', name: 'nokrelationship', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                        
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                        
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointgender', fieldLabel: getText('gender')+' <span style="color:red;">*</span>', name: 'jointgender', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointdateofbirth', fieldLabel: getText('dateofbirth')+' <span style="color:red;">*</span>', name: 'jointdateofbirth', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                        
                                        
                                //     ]
                                // },

                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                        
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointmaritalstatus', fieldLabel: getText('maritalstatus')+' <span style="color:red;">*</span>', name: 'jointmaritalstatus', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointnationality', fieldLabel: getText('nationality')+' <span style="color:red;">*</span>', name: 'jointnationality', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                        
                                        
                                //     ]
                                // },
                                
                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointreligion', fieldLabel: getText('religion')+' <span style="color:red;">*</span>', name: 'jointreligion', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                //         { xtype: 'textfield', flex: 3.3, reference: 'jointrace', fieldLabel: getText('race')+' <span style="color:red;">*</span>', name: 'jointrace', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                        
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                        
                                        
                                //     ]
                                // },

                                // {
                                //     xtype: 'panel',
                                //     border: false,
                                //     layout: 'hbox',
                                //     width: '90%',
                                //     items: [
                                //         //{ xtype: 'displayfield', hidden: false, reference: 'city', name: 'city' },
                                      
                                //         //{ xtype: 'textfield', flex: 3.3, reference: 'jointbumiputera', fieldLabel: getText('bumiputera')+' <span style="color:red;">*</span>', name: 'jointbumiputera', width: '90%', labelWidth: 150, margin: '0 10 10 0', allowBlank: false,},
                                        
                                //         // {
                                //         //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //         //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //         //     forceSelection: true, editable: false, allowBlank: false
                                //         // },
                                        
                                        
                                //     ]
                                // },
                                // { xtype: 'textarea', reference: 'nokaddress', fieldLabel: getText('address')+' <span style="color:red;">*</span>', name: 'nokaddress', width: '90%', labelWidth: 150, allowBlank: false},
                                // { xtype: 'textfield', reference: 'nokrelationship', fieldLabel: getText('relationship')+' <span style="color:red;">*</span>', name: 'nokrelationship', width: '90%', labelWidth: 150, allowBlank: false},
                                // {
                                //     xtype: 'combobox', fieldLabel: getText('relationship')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //     name: 'nokrelationship', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //     width: '90%', labelWidth: 150, ZforceSelection: true, editable: false, allowBlank: false
                                // },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                // item 2
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-bankaccountinfo",
                    title: getText('bankaccountinformation'),
                    items: [
                        {
                           
                            items: [
                                // { xtype: 'textfield', fieldLabel: getText('bankaccount'), name: 'bankaccount', width: '90%', labelWidth: 150, allowBlank: true},
                                // {
                                //     xtype: 'combobox', 
                                //     width: '90%', labelWidth: 150,
                                //     reference: 'bankaccount',
                                //     fieldLabel: getText('bankaccount')+' <span style="color:red;">*</span>',
                                //     store: Ext.getStore('bankaccounts').load(),
                                //     queryMode: 'local',
                                //     remoteFilter: false,
                                //     name: 'bankaccounts',
                                //     valueField: 'id',
                                //     displayField: 'value',
                                //     forceSelection: true, editable: false,
                                //     allowBlank: false,
                                //     editable: false,
                                //     renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
               
                                //         var productitems = Ext.getStore('bankaccounts').load();
                                //         console.log(productitems);
                                //         var catRecord = productitems.findRecord('id', value);
                                //         return catRecord ? catRecord.get('value') : '';
                                //     },
                                // },
                                { xtype: 'displayfield', reference: 'evidencecode', fieldLabel: 'Evidence Code', name: 'evidencecode', width: '90%', labelWidth: 150},
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    items: [
                                        { xtype: 'displayfield', flex: 3, reference: 'address', fieldLabel: getText('address'), name: 'address', width: '90%', labelWidth: 150, allowBlank: true},
                                    ]
                                },
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    items: [
                                        // { xtype: 'textfield', flex: 0.3, reference: 'city', fieldLabel: getText('city')+' <span style="color:red;">*</span>', name: 'city', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                        { xtype: 'displayfield', flex: 3, reference: 'postcode', fieldLabel: getText('postcode'), name: 'postcode', width: '90%', labelWidth: 150, allowBlank: true,
                                            minLength     : 5,
                                            maxLength     : 5,
                                            enforceMinLength : true,
                                            enforceMaxLength : true,
                                        },
                                        { xtype: 'displayfield', flex: 3, reference: 'state', fieldLabel: getText('state'), name: 'state', width: '90%', labelWidth: 150, allowBlank: true,
                                            minLength     : 5,
                                            maxLength     : 5,
                                            enforceMinLength : true,
                                            enforceMaxLength : true,
                                        },
                                        { xtype: 'displayfield', flex: 3, reference: 'city', fieldLabel: 'City', name: 'city', width: '90%', labelWidth: 150, allowBlank: true,
                                            minLength     : 5,
                                            maxLength     : 5,
                                            enforceMinLength : true,
                                            enforceMaxLength : true,
                                        },
                                        // {
                                        //     xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                        //     name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                        //     forceSelection: true, editable: false, allowBlank: false
                                        // },
                                    ]
                                },
                                { xtype: 'hidden', hidden: true, reference: 'accounttype', name: 'accounttype' },
                                { xtype: 'hidden', reference: 'bankaccountnumber', fieldLabel: getText('bankaccountnumber')+' <span style="color:red;">*</span>', name: 'bankaccountnumber', width: '90%', labelWidth: 150, allowBlank: false},
                                { 
                                    flex:1,
                                    xtype:'combobox',
                                    // fieldLabel: getText('bankaccountnumber'),
                                    fieldLabel: 'Bank Account Number',
                                    cls:'combo_box',
                                    store: {
                                        fields: ['accountnumber', 'accounttypestr'],
                                        // data : [
                                        //     {"accno":"3192301412", "name":"Joint Account"},
                                        
                                            
                                        // ]
                                    },
                                    tpl: [
                                        '<ul class="x-list-plain">',
                                        '<tpl for=".">',
                                        // '<li class="',
                                        // Ext.baseCSSPrefix, 'grid-group-hd ',
                                        // Ext.baseCSSPrefix, 'grid-group-title">{accno}</li>',
                                        '<li class="x-boundlist-item">',
                                        '<span class="fa fa-circle x-color-{accountstatuscolor}"></span> ',
                                        '{accountnumber} - {accounttypestr}',
                                        '</li>',
                                        '</tpl>',
                                        '</ul>'
                                    ],
                                    listeners: {
                                        // select: function(combo, records, eOpts) {
                                        //     accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                                        //     newText = "Enter " + records.data.name + " here"
                                        //     accountholdersearch.setEmptyText(newText);
                                        // }
                                    },
                                    reference: 'casaaccountlist',
                                    queryMode: 'local',
                                    displayField: 'accountnumber',
                                    valueField: 'accno',
                                    forceSelection: true,
                                    editable: false,
                                    margin: "0 10 0 10",
                                    listeners: {
                                        // select: {
                                        //     fn: 'showRegistrationForm'
                                        // }
                                        select: function(combo, record) {
                                            var bankAccountNumberField = this.up().up().down('[reference=bankaccountnumber]');
                                            bankAccountNumberField.setValue(record.get('accountnumber'));
                                        }
                                    }
                                },
                                { xtype: 'hidden', reference: 'accounttypestr', fieldLabel: getText('accounttype')+' <span style="color:red;">*</span>', name: 'accounttypestr', width: '90%', labelWidth: 150, allowBlank: false},
                                // { xtype: 'textfield', fieldLabel: getText('occupationcategory'), name: 'occupationcategory', width: '90%', labelWidth: 150, },
                                // {
                                //     xtype: 'combobox', width: '90%', labelWidth: 150, fieldLabel: getText('occupationcategory'), store: Ext.create('snap.store.OccupationCategory'), queryMode: 'local', remoteFilter: false,
                                //     name: 'occupationcategory', valueField: 'id', displayField: 'value', reference: 'occupationcategory',
                                //     forceSelection: true, editable: false, allowBlank: false
                                // },
                                // {
                                //     xtype: 'combobox', 
                                //     width: '90%', labelWidth: 150,
                                //     fieldLabel: getText('occupationcategory')+' <span style="color:red;">*</span>',
                                //     store: Ext.getStore('occupationcategory').load(),
                                //     queryMode: 'local',
                                //     remoteFilter: false,
                                //     name: 'occupationcategory',
                                //     reference: 'occupationcategory',
                                //     valueField: 'id',
                                //     displayField: 'value',
                                //     forceSelection: true, editable: false,
                                //     allowBlank: false,
                                //     renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
               
                                //         var productitems = Ext.getStore('occupationcategory').load();
                                //         console.log(productitems);
                                //         var catRecord = productitems.findRecord('id', value);
                                //         return catRecord ? catRecord.get('value') : '';
                                //     },
                                //     listeners: {
                                //         change: function(field, newVal, oldVal) {
             
                                //             // do checking, if newval is found in occupationsubcategory, show field.
                                //             // else hide field if not found in subcategory
                                //             sub = Ext.getStore('occupationsubcategory').load();
                                //             // check if subcategory exists
                                //             subcategory = sub.findRecord('occ_id', newVal);

                                //             // if return, check if exact match
                                //             match = false;
                                //             if(subcategory){
                                          
                                //                 if (subcategory.data.occ_id == newVal){
                                //                     match = true;
                                //                 }else{
                                //                     match = false;
                                //                 }
                                //             }
                                            
                                //             // clear filter
                                //             this.lookupController().lookupReference('occupationsubcategory').store.clearFilter()

                                //             if(match){
                                //                 this.lookupController().lookupReference('occupationsubcategory').show();
                                //             }else{
                                //                 this.lookupController().lookupReference('occupationsubcategory').setValue(null);
                                //                 this.lookupController().lookupReference('occupationsubcategory').hide();
                                //             }
                                            
                                //         }
                                //     }
     
                                // },
                                // {
                                //     xtype: 'combobox', width: '90%', labelWidth: 150, fieldLabel: getText('occupationsubcategory'), store: Ext.create('snap.store.OccupationSubCategory'), queryMode: 'local', remoteFilter: false,
                                //     name: 'occupationsubcategory', valueField: 'id', displayField: 'value', reference: 'occupationsubcategory',
                                //     forceSelection: true, editable: false, allowBlank: false
                                // },
                                // {
                                //     xtype: 'combobox', 
                                //     width: '90%', labelWidth: 150,
                                //     fieldLabel: getText('occupationsubcategory')+' <span style="color:red;">*</span>',
                                //     store: Ext.getStore('occupationsubcategory').load(),
                                //     queryMode: 'local',
                                //     remoteFilter: false,
                                //     name: 'occupationsubcategory',
                                //     reference: 'occupationsubcategory',
                                //     valueField: 'id',
                                //     displayField: 'value',
                                //     forceSelection: true, editable: false,
                                //     allowBlank: true,
                                //     hidden: true,
                                //     // store: {
                                //     //     filters: [{
                                //     //         property: 'id',
                                //     //         value: 4,
                                //     //     }]
                                //     // },
                                //     listeners: {
                                //         // filter fields by selected occupationid
                                //         expand: function(combo){
                                //             // combo.store.load({
                                //             //     //page:2,
                                //             //     start: 0,
                                //             //     limit: 1500
                                //             // })
                                //             combo.store.clearFilter();
                                //             combo.store.filter("occ_id", this.lookupController().lookupReference('occupationcategory').value);
                                          
                                //             // combo.store.filter("group", myView.partnerId);
                                //         }
                                //     },
                                //     renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                //         var productitems = Ext.getStore('occupationsubcategory').load();
                                //         console.log(productitems);
                                //         var catRecord = productitems.findRecord('id', value);
                                    
                                //         return catRecord ? catRecord.get('value') : '';
                                //     },
                                // },
                                // { xtype: 'textarea', fieldLabel: 'Description', name: 'description', width: '90%' },
                                //{ xtype: 'textfield', fieldLabel: 'Content', name: 'content', width: '90%' },
                                //{ xtype: 'textfield', fieldLabel: 'Content Repo', name: 'contentrepo', width: '90%' },
                                // { xtype: 'textfield', fieldLabel: getText('referralsalespersoncode') +' <span style="color:red;">*</span>', reference: 'referralsalespersoncode', name: 'referralsalespersoncode', width: '90%', labelWidth: 150, allowBlank: false, maxLength: 15,},
                                // { xtype: 'textfield', fieldLabel: 'Introducer/ Referral Code <span style="color:red;">*</span>', reference: 'referralintroducercode',  name: 'referralintroducercode', width: '90%', labelWidth: 150, allowBlank: false, maxLength: 15,},
                                {
                                    xtype: 'combobox',
                                    fieldLabel: 'Introducer/ Referral Code <span style="color:red;">*</span>',
                                    store: Ext.create('Ext.data.Store', {
                                        fields: ['value', 'text'],
                                        autoLoad: true, // Automatically load the data
                                        proxy: {
                                            type: 'ajax',
                                            url: 'index.php?hdl=uploadfilehandler&action=getfileTXT',
                                            reader: {
                                                type: 'json',
                                                rootProperty: 'data'
                                            }
                                        },
                                        listeners: {
                                            load: function(store, records) {
                                                var options = [];
                                                records.forEach(function(record) {
                                                    options.push({
                                                        value: record.get('value'),
                                                        text: record.get('text')
                                                    });
                                                });
                                                this.setData(options);
                                            }
                                        }
                                }),
                                displayField: 'text',
                                valueField: 'value',
                                queryMode: 'local',
                                reference: 'referralintroducercode',
                                name: 'referralintroducercode',
                                width: '90%',
                                labelWidth: 150,
                                allowBlank: false,
                                maxLength: 15,
                                editable: false
                                   
                                },
                                { xtype: 'textfield', fieldLabel: getText('campaigncode'), reference: 'campaigncode', name: 'campaigncode', width: '90%', labelWidth: 150, },
                                { xtype: 'textfield', fieldLabel: getText('remarks'), reference: 'statusremarks', name: 'statusremarks', width: '90%', labelWidth: 150, },
                                //{ xtype: 'combobox', fieldLabel:'Announcement Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type', valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                // Item 3
                // {
                //     layout: {
                //         type: 'table',
                //         columns: 3,
                //         tableAttrs: {
                //             style: {
                //                 width: '100%',
                //                 height: '100%',
                //                 top: '10px',
                //             },
                //         },
                //         tdAttrs: {
                //             valign: 'top',
                //             height: '100%',
                //             'background-color': 'grey',
                //         }
                //     },
                //     xtype: 'form',
                //     scrollable: false,
                //     defaults: {
                //         bodyPadding: '5',
                //     },
                //     reference: "register-form-password",
                //     title: getText('setpassword'),
                //     items: [
                //         {
                //             xtype: 'panel',
                //             border: false,
                //             layout: 'hbox',
                //             width: '90%',
                        
                //             items: [
                //                 {
                //                     xtype: 'panel',
                //                     // defaults: {
                //                     //   labelStyle: 'font-weight:bold',
                //                     // },
                //                     // layout: {
                //                     //     type: 'vbox',
                //                     //     align: 'center',
                //                     //     pack: 'center'
                //                     // },
                //                     flex:1, 
                //                     items: [
                //                         { xtype: 'textfield', inputType: 'password', labelWidth: 150, flex: 0.5, fieldLabel: getText('enterpassword')+' <span style="color:red;">*</span>', name: 'enterpassword', width: '90%', margin: '0 10 0 0', 
                //                             minLength     : 8,
                //                             maxLength     : 30,
                //                             enforceMinLength : true,
                //                             enforceMaxLength : true,
                //                         },
                //                         { xtype: 'displayfield', reference: "password_error_1", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: getText('confirmpassword'), name: 'enterpassword', width: '90%', margin: '0 10 0 0', },
                                      
                //                     ]
                //                 },
                //                 {
                //                     xtype: 'panel',
                //                     // defaults: {
                //                     //   labelStyle: 'font-weight:bold',
                //                     // },
                //                     // layout: {
                //                     //     type: 'vbox',
                //                     //     align: 'center',
                //                     //     pack: 'center'
                //                     // },
                //                     flex:1, 
                //                     items: [
                //                         { xtype: 'textfield', inputType: 'password', labelWidth: 150, flex: 0.5, fieldLabel: getText('confirmpassword')+' <span style="color:red;">*</span>', name: 'confirmpassword', width: '90%', margin: '0 10 0 0', 
                //                             minLength     : 8,
                //                             maxLength     : 30,
                //                             enforceMinLength : true,
                //                             enforceMaxLength : true,
                //                         },
                //                         { xtype: 'displayfield', reference: "password_error_2", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: getText('confirmpassword'), name: 'confirmpassword', width: '90%', margin: '0 10 0 0', },
                //                     ]
                //                 },
                               
                                
                    
                //                 { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                //             ]
                //         },
                       
                //     ]
                // },
                
                // Final item
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    title: getText('securitypin'),
                    reference: "register-form-pin",
                    items: [
                        {
                            layout: 'vbox',
                            items: [
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    flex: 1,
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            name: 'pincode',
                                            fieldLabel: getText('pincode')+' <span style="color:red;">*</span>',
                                            flex: 0.3,
                                            labelWidth: 150,
                                        },
                                        { xtype: 'textfield', name: "init_pin_1", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            reference: 'init_pin_1',
                                            inputType: 'password',
                                            flex: 0.12,
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                    // debugger;
                                                    this.lookupController().lookupReference('init_pin_2').focus();
                                                }
                                                // 'keyup':function(field, event){
                                    
                                                //     if(event.getKey() >= 65 && event.getKey() <= 90) {
                                                //        //the key was A-Z
                                                //     }
                                                //     if(event.getKey() >= 97 && event.getKey() <= 122) {
                                                //        //the key was a-z
                                                //     }
                                                // }
                                            }
                                        },
                                        { xtype: 'textfield', name: "init_pin_2", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            reference: 'init_pin_2',
                                            inputType: 'password',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('init_pin_3').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "init_pin_3", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            reference: 'init_pin_3',
                                            inputType: 'password',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('init_pin_4').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "init_pin_4", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            reference: 'init_pin_4',
                                            inputType: 'password',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('init_pin_5').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "init_pin_5", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            reference: 'init_pin_5',
                                            inputType: 'password',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('init_pin_6').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "init_pin_6", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            reference: 'init_pin_6',
                                            inputType: 'password',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.callParent(arguments);
                                                }
                                            }
                                        },
                                        { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                                    ]
                                },
                                { xtype: 'displayfield', reference: "pin_error_1", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: '', name: 'pin_error_1', width: '90%', margin: '0 10 0 0', },
                            ]
                        },
                        {
                            layout: 'vbox',
                            items: [
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    reference: "register-panel-confirmpin",
                                    flex: 1,
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            name: 'confirmpincode',
                                            fieldLabel: getText('confirmpincode')+' <span style="color:red;">*</span>',
                                            flex: 0.3,
                                            labelWidth: 150,
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_1", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_1',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('confirm_pin_2').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_2", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_2',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('confirm_pin_3').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_3", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_3',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('confirm_pin_4').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_4", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_4',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('confirm_pin_5').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_5", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_5',
                                            listeners: {
                                                change: function(field, newVal, oldVal) {
                                                
                                                    this.lookupController().lookupReference('confirm_pin_6').focus();
                                                }
                                            }
                                        },
                                        { xtype: 'textfield', name: "confirm_pin_6", width: '5%',
                                            maskRe: /[0-9-]/,
                                            maxLength: 1,
                                            enforceMaxLength: true,
                                            flex: 0.12,
                                            inputType: 'password',
                                            reference: 'confirm_pin_6',
                                            listeners: {
                                                onChange: function(newVal, oldVal) {
                                                
                                                    this.callParent(arguments);
                                                }
                                            }
                                        },
                                        { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                                    ]
                                },
                                { xtype: 'displayfield', reference: "pin_error_2", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: '', name: 'pin_error_2', width: '90%', margin: '0 10 0 0', },
                            ]
                        }
                    ]
                },
                
                ],
                // docked item button
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
                    cls: 'otc-main-form-button-bottom',
                    items: [{
                        text: getText('submit'),
                        //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                        // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        flex: 4,
                        tooltip: getText('submit'),
                        reference: 'next',
                        handler: 'nextButton'
                        
                    }],
                }]
            },
            // End test
           
        ]
    },
    formOtcApproval: {
        formDialogWidth: 950,
        controller: 'otcregister-otcregister',

        formDialogTitle: 'Approve Registration',

        // Settings
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
            //1st hbox
            {
                items: [
                    // { xtype: 'hidden', hidden: true, name: 'id' },
                    // {
                    //     itemId: 'user_main_fieldset',
                    //     xtype: 'fieldset',
                    //     title: 'Main Information',
                    //     title: 'Account Holder Details',
                    //     layout: 'hbox',
                    //     defaultType: 'textfield',
                    //     fieldDefaults: {
                    //         anchor: '100%',
                    //         msgTarget: 'side',
                    //         margin: '0 0 5 0',
                    //         width: '100%',
                    //     },
                    //     items: [
                    //         {
                    //             xtype: 'fieldcontainer',
                    //             fieldLabel: '',
                    //             defaultType: 'textboxfield',
                    //             layout: 'hbox',
                    //             items: [
                    //                 {
                    //                     xtype: 'displayfield', allowBlank: false, fieldLabel: 'Order No', reference: 'ordorderno', name: 'ordorderno', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                    //                 },
                    //                 {
                    //                     xtype: 'displayfield', allowBlank: false, fieldLabel: 'Total Amount', reference: 'ordamount', name: 'ordamount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                    //                 },
                    //             ]
                    //         }
                    //     ]
                    // },
                    // {
                    //     xtype: 'fieldset', 
                    //     title: 'Enter Approval Code', 
                    //     collapsible: false,
                    //     id: 'approvalfieldset',
                    //     items: [
                    //         {
                    //             xtype: 'fieldcontainer',
                    //             layout: {
                    //                 type: 'hbox',
                    //             },
                    //             items: [
                    //                 {
                    //                     xtype: 'textfield', fieldLabel: '', name: 'approvalcode', flex: 2, style: 'padding-left: 20px;', 
                    //                 },
                    //             ]
                    //         },
                    //     ]
                    // },
                    // {
                    //     xtype: 'form',
                    //     reference: 'searchresultsforpep-form',
                    //     border: false,
                    //     items: [
                    //         {
                    //             title: '',
                    //             flex: 13,
                    //             xtype: 'mypepmatchdataview',
                    //             reference: 'mypepematchdata',
                    //             enablePagination: false

                    //         },
                    //     ],
                    // },
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
