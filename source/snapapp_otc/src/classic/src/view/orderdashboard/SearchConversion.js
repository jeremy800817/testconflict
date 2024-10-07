
Ext.define('snap.view.orderdashboard.SearchConversion',{
    extend: 'snap.view.orderdashboard.OrderDashboard_ALRAJHI',
    xtype: 'searchconversionview',

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
                              flex: 1,
                              xtype: 'button',
                              text: 'Convert',
                              reference: 'convertButton',
                              width: '90%',
                              style: 'margin-top:55px',
                              handler: function (button) {

                                // if(vm.get('profile-fullname') == '-'){
                                //   Ext.MessageBox.show({
                                //       title: 'Error Message',
                                //       msg: 'Please select a user record',
                                //       buttons: Ext.MessageBox.OK,
                                //       icon: Ext.MessageBox.ERROR
                                //   });
                                //   return;
                                // }

                                  var convertNowWindow = Ext.create('Ext.window.Window', {
                                      title: 'Convert Now Confirmation',
                                      width: 400,
                                      height: 150,
                                      layout: 'center',
                                      modal: true,
                                      items: [{
                                          xtype: 'container',
                                          layout: 'hbox',
                                          items: [{
                                              xtype: 'button',
                                              text: 'Verify Biometrics',
                                              margin: '0 10 0 0',
                                              handler: function () {
                                                  elmnt.doBiometricValidation(button, 'conAqad', 'Conversion');
                                                  convertNowWindow.close();
                                              }
                                          }, {
                                              xtype: 'button',
                                              text: 'Biometric Unavailable',
                                              handler: function () {
                                                  elmnt.doBiometricSkip(button, 'conAqad', '0', 'Conversion');
                                                  convertNowWindow.close();
                                              }
                                          }]
                                      }]
                                  });
                          
                                  convertNowWindow.show();
                              }
                            }
                          
                          

                        ],
                
                    },
                ]
      
            },
        ]
    },

});
