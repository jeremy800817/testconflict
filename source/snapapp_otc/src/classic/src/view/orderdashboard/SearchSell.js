
Ext.define('snap.view.orderdashboard.SearchSell',{
    extend: 'snap.view.orderdashboard.OrderDashboard_ALRAJHI',
    xtype: 'searchsellview',

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
                                    // balanceafterconversion = record.data.goldbalance - elmnt.lookupReference('totalconversionvalue').value;
                                    // balanceafterconversion = parseFloat(balanceafterconversion).toFixed(3);
                                    // elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion: 0.000);
                                    
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
                                    
                                    // getDisplayController.lookupReference('profile-goldbalance').setValue((record.data.goldbalance ? record.data.goldbalance : 0)+ 'g');
                                    // getDisplayController.lookupReference('profile-avgbuyprice').setValue('RM' + (record.data.avgbuyprice ? record.data.avgbuyprice : 0)+ '/g');
                                    // getDisplayController.lookupReference('profile-totalcostgoldbalance').setValue('RM' + (record.data.totalcostgoldbalance ? record.data.totalcostgoldbalance : 0));
                                    // getDisplayController.lookupReference('profile-diffcurrentpriceprcetage').setValue((record.data.diffcurrentpriceprcetage ? record.data.diffcurrentpriceprcetage : 0) + '%');
                                    // getDisplayController.lookupReference('profile-currentgoldvalue').setValue('RM' + (record.data.currentgoldvalue ? record.data.currentgoldvalue : 0));

                                    // Additional fields
                                    getDisplayController.lookupReference('profile-accountnumber').setValue(record.data.accountnumber);
                                    getDisplayController.lookupReference('profile-partnercusid').setValue(record.data.partnercusid);
                                    getDisplayController.lookupReference('profile-partnername').setValue(record.data.partnername);
                                    getDisplayController.lookupReference('profile-accounttype').setValue(record.data.accounttypestr);

                                    // Get transaction
                                    // ordersearchgrid = getDisplayController.lookupReference('myorder');
                                    // ordersearchgrid.getStore().proxy.url = 'index.php?hdl=myorder&action=getOtcOrders&mykadno='+record.data.mykadno+'&partnerid='+record.data.partnerid+'&accountholdercode='+record.data.accountholdercode;
                                    // ordersearchgrid.getStore().reload();
                             
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
                          var hasSellGoldPermission = snap.getApplication().hasPermission('/root/alrajhi/search/sell');
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
                        layout: {
                            pack: 'center',
                            type: 'hbox',
                        },
                        cls: 'otc-main-center-price-button-sell',
                        items: [{
                            text: 'Sell Now',
                            flex: 4,
                            tooltip: 'Sell Gold',
                            reference: 'Sell Now',
                            handler: function (button) {

                                // if(vm.get('profile-fullname') == '-'){
                                //     Ext.MessageBox.show({
                                //         title: 'Error Message',
                                //         msg: 'Please select a user record',
                                //         buttons: Ext.MessageBox.OK,
                                //         icon: Ext.MessageBox.ERROR
                                //     });
                                //     return;
                                // }

                                var sellNowWindow = Ext.create('Ext.window.Window', {
                                    title: 'Sell Now Confirmation',
                                    width: 400,
                                    height: 150,
                                    layout: 'center',
                                    modal: true,
                                    items: [{
                                        xtype: 'container',
                                        layout: {
                                            type: 'hbox',
                                            pack: 'center'
                                        },
                                        items: [{
                                            xtype: 'button',
                                            text: 'Verify Biometric',
                                            margin: '0 10 0 0', // Adding a margin on the right to create a gap
                                            handler: function () {
                                                elmnt.doBiometricValidation(button, 'doSpotOrderSellOTC', 'Sell');
                                                sellNowWindow.close();
                                            }
                                        }, {
                                            xtype: 'button',
                                            text: 'Biometric Unavailable',
                                            handler: function () {
                                                elmnt.doBiometricSkip(button, 'doSpotOrderSellOTC',  '0' , 'Sell');
                                                sellNowWindow.close();
                                            }
                                        }]
                                    }]
                                });
                    
                                sellNowWindow.show();
                            }
                        }],
                    }],
                    
                },]

            // id: 'medicalrecord',
            }, 
            
        ]
    },

});
