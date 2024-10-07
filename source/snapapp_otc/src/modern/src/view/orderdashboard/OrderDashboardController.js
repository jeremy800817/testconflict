Ext.define('snap.view.orderdashboard.OrderDashboardController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.orderdashboard-orderdashboard',


    onPreLoadViewDetail: function (record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'order', action: 'detailview', id: record.data.id })
            .then(function (data) {
                if (data.success) {
                    displayCallback(data.record);
                }
            })
        return false;
    },

    doSpotOrderSell: function (elemnt) {
        var me = this;

        //var form = elemnt.lookupController().lookupReference('spotorder-form');
        var form = elemnt.up('formpanel');

        if (!form.isValid()) {
            Ext.Msg.alert('Error Message', 'Some field value is invalid', Ext.emptyFn);
            return;
        }

        // Create forms
        spotorder = form.getValues();
        //futureorder = form2.getFieldValues();
        productspotvalue = Ext.getCmp('productspot');

        // Total value to decimal 
        // Check Total Value     
        
        if (spotorder.totalvalue != null && spotorder.totalvalue != '') {            
            totalvalue = parseFloat(spotorder.totalvalue).toFixed(2);
        } else {
            totalvalue = 0;
        }
        // Check total xau weight
        if (spotorder.totalxauweight != null && spotorder.totalxauweight != '') {           
            totalxauweight = parseFloat(spotorder.totalxauweight).toFixed(3);
        } else {
            totalxauweight = 0;
        }        

        // Check if product is not selected 
        if (spotorder.product == null) {
            Ext.Msg.alert('Error Message', 'Product field is required', Ext.emptyFn);
        }


        // Initialize product
        product = spotorder.product;

        /*------------------------ Ace Buy Display ------------------------------------*/

        // Acquire Ace Buy 
        //acebuy = '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">' + 10 + '</span>';
        acebuy = spotorder.acebuyprice;

        // Slice Ace values
        acebuy = parseFloat(acebuy).toFixed(3);
        acebuy = acebuy.toString();
        acebuytruncatedleft = acebuy.slice(0, -5);
        //aceselltruncatedleft = parseFloat(aceselltruncatedleft);

        // Enlarged values of ace buy/sell (last 4)
        acebuytruncatedright = acebuy.substring(acebuy.length - 5, acebuy.length);
        //aceselltruncatedright = parseFloat(aceselltruncatedright);

        /*------------------------ Ace Buy Price Confirmation------------------------------------*/
        // Set Color Codes
        // If value > previous
        if (spotorder.acebuypricechange = 'green') {
            // Green 
            backgroundcolor = '<div style="padding: 2.7em;background-color:#A7EAAC;">';
        } else if (spotorder.acebuypricechange = 'red') {
            // If value < previous
            // Red
            backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
        } else if (spotorder.acebuypricechange = 'grey') {
            // If no change
            backgroundcolor = '<div style="padding: 2.7em;background-color:#8BA2AF;">';
        }

        // Old Design
        /*
        acebuydesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
        '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
        'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' + 
        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';*/

        // New Design
        acebuydesignconfirmation = backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' +
            acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' +
            '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
            '</div>';

        // End of Ace Buy Price Confirmation
        // End of Ace Buy 
        /*--------------------------------------------- Check Backend if requirements are met ----------------------------------------*/

        // Get orderfees
        orderfees = vm.get('fees');
        fee = orderfees.find(x => x.id === spotorder.product);

        // Set Refinery Fee (temp)
        if (fee.refineryfee != null) {
            refineryfeeraw = parseFloat(fee.refineryfee).toFixed(2);
        } else {
            refineryfeeraw = parseFloat(0).toFixed(2);
        }

        // Final Final Ace Buy Price
        acebuyprice = parseFloat(Ext.getCmp('acebuyprice').getRawValue()).toFixed(3);
        finalacebuyprice = parseFloat(acebuyprice - refineryfeeraw).toFixed(3);


        /*-------------------- Math ---------------------- */

        // Total Value inserted
        // Find Weight
        //totalestvalue = 100
        //finalbuyprice = 256.55
        totalestvalue = 0;
        // How to get final buy price?
        // Ace buy price - refinery fee = Final buy price 

        // Est value / final buy price = xau weight
        // When total xau weight is 0, means it is value
        if (totalxauweight == 0) {
            finaltotalxauweight = parseFloat(totalvalue / finalacebuyprice).toFixed(3);
            totalestvalue = parseFloat(totalvalue).toFixed(2);
            finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
        }

        // Xau Weight inserted
        // Find Total Est Value
        // Ace buy price - refinery fee = final buy price

        // Final buy price  * xau weight = total est value
        if (totalestvalue == 0) {
            totalestvalue = parseFloat(finalacebuyprice * totalxauweight).toFixed(2);
            finaltotalxauweight = totalxauweight;
        }


        // Acquire Refinery fee
        refineryfee = '<span style="color:#404040;font: 900 26px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">' + refineryfeeraw + '</span>';
        //debugger;
        //var aa= Ext.JSON.encode(params);
        //alert(aa);

        // Ui height
        var height = Ext.getBody().getViewSize().height;

        var cardHeight = height * 0.8;

        var boxHeight = height * 0.2;

        // Panel for Total Value
        var spotpanelbuytotalvalue = new Ext.form.Panel({
            frame: true,
            //minHeight:'1000px',
            layout: {
                type: 'vbox',
                fullscreen: true,
                align: 'fit',
            },
            //overflow: 'y',           
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            maxHeight: cardHeight,
            items: [                
                {
                    
                    items: [                        
                        {
                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },
                            defaults: {
                                frame: false,
                            },

                            items: [
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">Product</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                        }
                                    },                                   
                                    frame: false,   
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items:[
                                        {                                           
                                            xtype: 'displayfield', name: 'Product', reference: 'productprice', value: productspotvalue.getRawValue(),      
                                            renderer: function (value) {
                                                this.setHtml('<span style="text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;">' + value + '</span>')
                                            }
                                        }
                                    ]
                                },   
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Value (RM)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,                                    
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'Value', reference: 'value', value: totalvalue,                                      
                                        renderer: function (value) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">' + value + '</span>')
                                        }
                                    },]
                                },
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,                                    
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'refineryfee', reference: 'refineryfee', value: refineryfee,                                      
                                        renderer: function (value) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">' + value + '</span>')
                                        }
                                    },]
                                },
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE BUY (RM)</span>',
                                    header: {
                                        style: {
                                            // // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',                                            
                                        }
                                    },                                   
                                    frame: false,                                    
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items:[
                                        {
                                            xtype: 'displayfield',                                    
                                            id: 'spotorderbuyconfirmationval',
                                            value: acebuydesignconfirmation,                                   
                                            style: 'text-align:center;box-sizing:border-box;border-radius:12px;',
                                            name: 'acebuy',
                                            maxHeight: boxHeight,
                                            renderer: function (html) {
                                                this.setHtml(html);                                                
                                            }
                                        },
                                    ]

                                }, 
                                // {
                                //     /* layout: {
                                //         type: 'hbox',
                                //         align: 'center',
                                //         pack: 'center'
                                //     }, */
                                //     html: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">REFINING FEE</span>',
                                //     header: {
                                //         style: {
                                //             backgroundColor: 'white',
                                //             display: 'inline-block',
                                //             color: '#000000',

                                //         }
                                //     },                                   
                                //     margin: '0 10 0 0',
                                //     items: [{
                                //         xtype: 'displayfield', name: 'refineryfee', reference: 'refineryfee', value: refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Refining Fee', flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ", renderer: function (html) {
                                //             this.setHtml(html)
                                //         }
                                //     },]
                                // },                         
                                
                                /* {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    
                                    frame: false,
                                    //style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [ {
                                        xtype : 'displayfield',
                                        width : '99%',
                                        padding: '0 1 0 1',
                                        value: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'></span></h5>",
                                        //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                                        
                                    },{
                                        //xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: '', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: '<span style="font: 900 30px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block">- </span>'+ refineryfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Refining Fee', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",renderer:function(html){
                                            this.setHtml(html)
                                        }
                                        
                                    },]
                                }, */
                                /*
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        //xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: '', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                        xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: '<span style="font: 900 30px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block">- </span>'+ refineryfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: '', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },*/
                            ]
                        },
                    ]
                },               
            ],
        });
        

        // Spot Panel for Total Xau Weight 
        var spotpanelbuytotalxauweight = new Ext.form.Panel({
            frame: true,
            layout: 'vbox',
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            maxHeight: cardHeight,
            items: [                
                {                   
                    items: [                        
                        {

                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },     
                            defaults: {
                                frame: false,
                            },
                            items: [
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,                                    
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{                                        
                                        xtype: 'displayfield', name: 'Product', reference: 'productprice', value: productspotvalue.rawValue, 
                                        renderer: function (html) {
                                            this.setHtml('<span style="text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;">'+html+'</span>')
                                        }
                                    },]
                                },{                                      
                                    html: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            backgroundColor: 'white',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:false,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',renderer:function(html){
                                        this.setHtml(html)
                                    },
                                    //title: 'Ask',
                                    //flex: 3,
                                    margin: '0 0 0 0',
                                    
                                },
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Total XAU Weight (gram)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,                                                                       
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'Value', reference: 'value', value: totalxauweight,                                        
                                        renderer: function (html) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">'+html+'</span>')
                                        }
                                    },]
                                },
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refinery Fee</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,                                                                       
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'refineryfee', reference: 'refineryfee', value: refineryfee,                                        
                                        renderer: function (html) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">'+html+'</span>')
                                        }
                                    },]
                                },
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE BUY (RM)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: false,
                                    flex: 3,
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items:[
                                        {
                                            xtype: 'displayfield',                                          
                                            id: 'spotorderbuyconfirmationxau',
                                            style: 'text-align:center;box-sizing:border-box;border-radius:12px;',
                                            name: 'acebuy',
                                            value: acebuydesignconfirmation,
                                            maxHeight: boxHeight,
                                            renderer: function (html) {
                                                this.setHtml(html)
                                            }
                                        },
                                    ]
                                },                                
                                // {                                    
                                //     frame: false,                                  
                                //     flex: 3,
                                //     margin: '0 10 0 0',
                                //     items: [{
                                //         xtype: 'displayfield',
                                //         width: '99%',
                                //         padding: '0 1 0 1',
                                //         html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 10px; font-size: 16px;color:#404040;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>REFINING FEE</span></h5>",                                      
                                //     }, {                                       
                                //         xtype: 'displayfield', name: 'refineryfee', reference: 'refineryfee', value: '<span style="font: 900 30px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block">- </span>' + refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Refining Fee', flex: 1, style: 'padding-left: 10px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ", renderer: function (html) {
                                //             this.setHtml(html)
                                //         }
                                //     },]
                                // },
                            ]
                        },
                    ]
                },               
            ],
        });

        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: 'Your request completed successfully.',
            layout: 'fit',
            //width: 400,
            width: '100%',
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {
                    xtype:'panel',
                    layout:'vbox',
                    title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1>',
                    header: {
                        style: {
                            backgroundColor: 'white',
                            display: 'inline-block',
                            color: '#000000',

                        }
                    },
                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    flex: 3,
                    margin: '0 10 0 0',
                    items: [
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Product:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'product', reference: 'product', value: productspotvalue.rawValue , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Product', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                }
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Final ACE Buy Price:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'finalprice', reference: 'finalprice', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',  style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">XAU Weight:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'xauweight', reference: 'xauweight', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Total est. value:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'totalestvalue', reference: 'totalestvalue', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                }
                            ]
                        },{
                            xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: ''
                        }                    
                ]
                },
            ],
            buttons: [{
                text: 'OK',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }, {
                text: 'Print PDF',
                handler: function (btn) {
                    me._printOrderPDFSpot(btn);
                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                }
            },
            closeAction: 'destroy',
            //items: spotpanelbuytotalxauweight
        });

        /* ---------------------------------- Panel Graphics --------------------------------- */
        //var type=selectedRecords[0].get('type');            
        var windowforspotorderbuytotal = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: '100%',
            //maxHeight: 700,                    
            modal: true,
            plain: true,
            //closeAction: 'destroy',
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function (btn) {
                    if (spotpanelbuytotalvalue.isValid()) {
                        btn.disable();
                        //var newvalue = '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + parseFloat(Ext.getCmp('acebuyprice').getRawValue() - refineryfeeraw).toFixed(3) + '</span>';    
                        spotpanelbuytotalvalue.submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: {
                                hdl: 'order', action: 'doSpotOrder',
                                buyprice: Ext.getCmp('acebuyprice').getRawValue(),
                                uuid: Ext.getCmp('orderuuid').getRawValue(),
                                amount: spotorder.totalvalue,
                                productitem: spotorder.product,
                                sellorbuy: 'buy',

                            },
                            waitMsg: 'Processing',
                            success: function (frm, action) { //success       
                                
                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderform').reset();

                                // Set Final Buy Price
                                windowforordercomplete.items.items[0].items.items[1].items.items[1].setValue(action.return[0].price);
                                windowforordercomplete.items.items[0].items.items[2].items.items[1].setValue(action.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));
                                
                                // Set Total Est Value
                                windowforordercomplete.items.items[0].items.items[3].items.items[1].setValue(action.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));                              
                                windowforordercomplete.items.items[0].items.items[4].setValue(action.return);
                                
                                owningWindow = btn.up('window');
                                windowforordercomplete.show();
                                owningWindow.close();
                                //Set values
                                // Set Final Ace Buy Price
                                // Final Final Ace Buy Price                                
                                //finalacebuypricefloat = parseFloat(this.params.buyprice);
                                // finalacebuypricefloat = parseFloat(Ext.getCmp('acebuyprice').getRawValue());
                                // finalacebuyprice = parseFloat(finalacebuypricefloat - refineryfeeraw).toFixed(3);
                                //windowforordercomplete.items.items[0].items.items[1].setValue(finalacebuyprice);

                                // Set Xau Weight
                                /*-------------------- Math ---------------------- */

                                // Total Value inserted
                                // Find Weight
                                //totalestvalue = 100
                                //finalbuyprice = 256.55

                                // finaltotalestvalue = 0;
                                // How to get final buy price?
                                // Ace buy price - refinery fee = Final buy price 

                                // Est value / final Sell price = xau weight
                                // When total xau weight is 0, means it is value
                                // if (totalxauweight == 0) {
                                //     finaltotalxauweight = parseFloat(totalvalue / finalacebuyprice).toFixed(3);
                                //     finaltotalestvalue = parseFloat(totalvalue).toFixed(2);
                                //     finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
                                // }

                                // Xau Weight inserted
                                // Find Total Est Value
                                // Ace buy price - refinery fee = final buy price

                                // Final buy price  * xau weight = total est value
                                // if (finaltotalestvalue == 0) {
                                //     finaltotalestvalue = parseFloat(finalacebuyprice * totalxauweight).toFixed(2);
                                //     finaltotalxauweight = totalxauweight;
                                // }
                                
                                //myView.getStore().reload();
                            },
                            failure: function (frm, action) {
                                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                btn.enable();
                                var errmsg = action.result.errorMessage;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    windowforordercomplete.show();
                                    errmsg = action.result.errorMessage;
                                }
                                //Ext.Msg.alert('Error Message', errmsg, Ext.emptyFn);
                            }
                        });
                    } else {
                        Ext.Msg.alert('Error Message', 'All fields are required', Ext.emptyFn);
                    }
                }
            }, {
                text: 'Close',
                handler: function (btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();


                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                }
            },
            closeAction: 'destroy',
            items: spotpanelbuytotalvalue
        });

        //var type=selectedRecords[0].get('type');            
        var windowforspotorderbuyxau = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function (btn) {
                    if (spotpanelbuytotalxauweight.isValid()) {
                        btn.disable();
                        spotpanelbuytotalxauweight.submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: {
                                hdl: 'order', action: 'doSpotOrder',
                                buyprice: Ext.getCmp('acebuyprice').getRawValue(),
                                uuid: Ext.getCmp('orderuuid').getRawValue(),
                                weight: spotorder.totalxauweight,
                                productitem: spotorder.product,
                                sellorbuy: 'buy',

                            },
                            waitMsg: 'Processing',
                            success: function (frm, action) { //success                                   


                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderform').reset();
                                // Set Final Buy Price

                                //Set values
                                // Set Final Ace Buy Price
                                // Final Final Ace Buy Price
                                //finalacebuypricefloat = parseFloat(this.params.buyprice);
                                // finalacebuypricefloat = parseFloat(Ext.getCmp('acebuyprice').getRawValue());
                                // finalacebuyprice = parseFloat(finalacebuypricefloat - refineryfeeraw).toFixed(3);
                                //windowforordercomplete.items.items[0].items.items[1].setValue(finalacebuyprice);
                                windowforordercomplete.items.items[0].items.items[1].items.items[1].setValue(action.return[0].price);
                                windowforordercomplete.items.items[0].items.items[2].items.items[1].setValue(action.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                // Set Total Est Value
                                //windowforordercomplete.items.items[0].items.items[3].setValue(finaltotalestvalue);
                                windowforordercomplete.items.items[0].items.items[3].items.items[1].setValue(action.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));
                                windowforordercomplete.items.items[0].items.items[4].setValue(action.return);
                                owningWindow = btn.up('window');

                                windowforordercomplete.show();

                                owningWindow.close();
                                // Set Xau Weight
                                /*-------------------- Math ---------------------- */

                                // Total Value inserted
                                // Find Weight
                                //totalestvalue = 100
                                //finalbuyprice = 256.55

                                // finaltotalestvalue = 0;
                                // How to get final buy price?
                                // Ace buy price - refinery fee = Final buy price 

                                // Est value / final Sell price = xau weight
                                // When total xau weight is 0, means it is value
                                // if (totalxauweight == 0) {
                                //     finaltotalxauweight = parseFloat(totalvalue / finalacebuyprice).toFixed(3);
                                //     finaltotalestvalue = parseFloat(totalvalue).toFixed(2);
                                //     finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
                                // }

                                // Xau Weight inserted
                                // Find Total Est Value
                                // Ace buy price - refinery fee = final buy price

                                // Final buy price  * xau weight = total est value
                                // if (finaltotalestvalue == 0) {
                                //     finaltotalestvalue = parseFloat(finalacebuyprice * totalxauweight).toFixed(2);
                                //     finaltotalxauweight = totalxauweight;
                                // }
                               // windowforordercomplete.items.items[0].items.items[2].setValue(finaltotalxauweight);
                                
                                // myView.getStore().reload();
                            },
                            failure: function (frm, action) {
                                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                btn.enable();
                                var errmsg = action.result.errorMessage;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    errmsg = action.result.errorMessage;
                                }
                                //Ext.Msg.alert('Error Message', errmsg, Ext.emptyFn);
                            }
                        });
                    } else {
                        Ext.Msg.alert('All fields are required', errmsg, Ext.emptyFn);
                    }
                }
            }, {
                text: 'Close',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();

                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                    // Destroy form
                    //Ext.getCmp('spotorderfinalpricebuy').destroy();

                }
            },
            closeAction: 'destroy',
            items: spotpanelbuytotalxauweight
        });
        /* ---------------------------------- End Panel Graphics --------------------------------- */
        /* ---------------------------------- Check Permission for Spot Order Sell --------------------------------- */
        // Get Permission 
        allpermissions = vm.get('permissions');
        productpermission = allpermissions.find(x => x.id === spotorder.product);
        //console.log(productpermission);


        // Run through form and check for empty fields
        var fields = form.getFields();
        var forminput = Object.values(fields);        

        // initialize variables for form condition checking
        checkvalue = 1;
        checkweight = 1;
        checkbuy = 1;
        checksell = 1;

        // weight checking
        doweightcheck = 0;
        checkweightdivisible = 0;

        // Initialize null checker
        checkvaluenull = 1;
        checkweightnull = 1;
        checkbuynull = 1;
        checksellnull = 1;

        // Set Error Messages
        /*
        errmsgvalue = 'Sorry, ACE Cannot Buy This Product By Amount';
        errmsgweight = 'Sorry, We Do Not Sell By Amount';
        errmsgbuy = 'Sorry, ACE Cannot Buy This Product By Weight';
        errmsgsell = 'Sorry, ACE Cannot Sell This Product By Weight';
        */

        validformbuytotal = 0;
        validformbuyweight = 0;

        // Begin Loop
        
        for (index = 0; index < forminput.length; index++) {
            //alert(forminput[index].value);
            // If field is not empty               
            if (forminput[index].rawValue != "") {

                // Begin checking for empty fields
                // If the filled fields are the 4 inputs
                // Check id
                if (forminput[index].id == 'totalvaluespotdashboard' || forminput[index].id == 'totalxauweightspotdashboard' || forminput[index].id == 'acebuyprice') {
                   
                    // Begin checking for permission
                    // Checking if fields are filled
                    // Then save input combination for message display
                    //alert("Not EMPTY!" + forminput[index].id);    
                    if (forminput[index].id == 'totalvaluespotdashboard' && (Ext.getCmp('totalvaluespotdashboard').isDisabled() == null || Ext.getCmp('totalvaluespotdashboard').isDisabled() == false) && totalvalue > 0){

                        // Value is not null
                        checkvaluenull = 0;
                        // Check for permission
                        // Display warning if not true                         
                        if (productpermission.bycurrency != true) {
                            // Set flag to indicate product has permission for value  
                            checkvalue = 0;

                        }
                        //alert("Can value " + productpermission.bycurrency);
                    }                                  
                    if (forminput[index].id == 'totalxauweightspotdashboard' && (Ext.getCmp('totalxauweightspotdashboard').isDisabled() == null || Ext.getCmp('totalxauweightspotdashboard').isDisabled() == false ) && totalxauweight > 0) {
                        // Checking if fields are filled
                        checkweightnull = 0;
                        // Check if weight is within reasonable range
                       
                        if (productpermission.weight != null) {
                            //debugger;
                            // Ignore weights that are 0                            
                            if (productpermission.weight != 0) {
                                // Check if modulus is 0
                                // Enable weight checking
                                doweightcheck = 1;                                
                                //debugger;
                                if ((forminput[index].rawValue % productpermission.weight) != 0) {
                                    checkweightdivisible = 0;
                                } else {
                                    checkweightdivisible = 1;
                                }
                               
                                //debugger;
                                //alert("this not 0, cant be divided");
                            }
                        }
                        //forminput[index].value/
                        if (productpermission.byweight != true) {
                            // Set flag to indicate product has permission for value     
                            checkweight = 0;

                        }
                        
                        //alert("Can weight" + productpermission.byweight);
                    }                    
                    if (forminput[index].id == 'acebuyprice' && (Ext.getCmp('acebuyprice').isDisabled() == null || Ext.getCmp('acebuyprice').isDisabled()==false) ){

                        // Checking if fields are filled
                        checkbuynull = 0;

                        if (productpermission.canbuy != true || productpermission.partnerCanSell != true) {
                            // Set flag to indicate product has permission for Buy
                            checkbuy = 0;

                        }
                        //alert("Can Buy " + productpermission.canbuy);
                    }
                    // End Checking for permission

                } else {
                    //Ext.getCmp(itemname).setHidden(true);
                } // End Checking for empty fields

            } // End Loop
        }
        
        // Do weight check
        if (doweightcheck == 1) {
            if (checkweightnull == 0 && checkbuynull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checkbuy == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checkbuy == 1 && checkweightdivisible == 1) {

                    // Weight = yes
                    // Sell = no
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if(checkweight == 0 && checkbuy == 0 && checkweightdivisible == 1){

                    // Weight = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }
                    

                } else if (checkweight == 1 && checkbuy == 0 && checkweightdivisible == 0) {

                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checkbuy == 0 && checkweightdivisible == 0) {

                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product');
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 1 && checkbuy == 1 && checkweightdivisible == 0) {

                    // Weight = yes
                    // Sell = no
                    Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);

                } else if (checkweight == 1 && checkbuy == 0 && checkweightdivisible == 1){

                    // Weight = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validformbuyweight = 1;
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        } else {
            // Check permission and display accordingly
            if (checkvaluenull == 0 && checkbuynull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkvalue == 0 && checkbuy == 1) {

                    // Value = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);

                } else if (checkvalue == 1 && checkbuy == 0) {

                    // Value = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else if (checkvalue == 0 && checkbuy == 0) {

                     // Value = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else {
                    validformbuytotal = 1;
                }


            } else if (checkweightnull == 0 && checkbuynull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checkbuy == 1) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 1 && checkbuy == 0) {

                    // Weight = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checkbuy == 0) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validformbuyweight = 1;
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        }

        /* ----------------------------------  End Check Permission for Spot Order Buy --------------------------------- */        
        
        if (totalvalue != null && totalxauweight == 0 && product != null && validformbuytotal == 1) {

            windowforspotorderbuytotal.show();


        } else if (totalvalue == 0 && totalxauweight != null && product != null && validformbuyweight == 1) {


            windowforspotorderbuyxau.show();


        } else {
            if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                Ext.getCmp('spotorderbuyconfirmationval').destroy();
            }
            if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                Ext.getCmp('spotorderbuyconfirmationxau').destroy();
            }
            if (Ext.getCmp('spotordersellconfirmationval') != null) {
                Ext.getCmp('spotordersellconfirmationval').destroy();
            }
            if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                Ext.getCmp('spotordersellconfirmationxau').destroy();
            }
        }
    },

    doSpotOrderBuy: function (elemnt) {
        var me = this;

        //var form = elemnt.lookupController().lookupReference('spotorder-form');
        var form =elemnt.up('formpanel');
        
        if (!form.isValid()) {
            Ext.Msg.alert('Error Message', 'Some field value is invalid', Ext.emptyFn);
            return;
        }

        // Create forms
        spotorder = form.getValues();
        //futureorder = form2.getFieldValues();
        productspotvalue = Ext.getCmp('productspot');

        // Total value to decimal 
        // Check Total Value
        if (spotorder.totalvalue != null && spotorder.totalvalue !='') {
            totalvalue = parseFloat(spotorder.totalvalue).toFixed(2);
        } else {
            totalvalue = 0;
        }
        // Check total xau weight
        if (spotorder.totalxauweight != null && spotorder.totalxauweight != '') {
            totalxauweight = parseFloat(spotorder.totalxauweight).toFixed(3);
        } else {
            totalxauweight = 0;
        }

        // Check if product is not selected 
        if (spotorder.product == null) {
            Ext.Msg.alert('Error Message', 'Product field is required', Ext.emptyFn);
        }

        // Init product
        product = spotorder.product;

        /*------------------------ Ace Buy Display ------------------------------------*/

        // Acquire Ace Buy 
        //acebuy = '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">' + 10 + '</span>';
        acesell = spotorder.acesellprice;

        // Slice Ace values
        acesell = parseFloat(acesell).toFixed(3);
        acesell = acesell.toString();
        aceselltruncatedleft = acesell.slice(0, -5);
        //aceselltruncatedleft = parseFloat(aceselltruncatedleft);


        // Enlarged values of ace buy/sell (last 4)
        aceselltruncatedright = acesell.substring(acesell.length - 5, acesell.length);

        /*------------------------ Ace Sell Price Confirmation------------------------------------*/
        // Set Color Codes
        // If value > previous
        if (spotorder.acesellpricechange = 'green') {
            // Green 
            backgroundcolor = '<div style="padding: 2.7em;background-color:#A7EAAC;">';
        } else if (spotorder.acesellpricechange = 'red') {
            // If value < previous
            // Red
            backgroundcolor = '<div style="padding: 2.7em;background-color:#F99B9B;">';
        } else if (spotorder.acesellpricechange = 'grey') {
            // If no change
            backgroundcolor = '<div style="padding: 2.7em;background-color:#8BA2AF;">';
        }

        // Old sell
        /*
        aceselldesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
        '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
        'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' + 
        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';
`       */
        aceselldesignconfirmation = backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' +
            aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' +
            '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
            '</div>';

        // End of Ace Sell Price Confirmation
        // End of Ace Sell
        // Get orderfees
        orderfees = vm.get('fees');
        fee = orderfees.find(x => x.id === spotorder.product);

        // Set Premium Fee (temp)
        if (fee.premiumfee != null) {
            premiumfeeraw = parseFloat(fee.premiumfee).toFixed(2);
        } else {
            premiumfeeraw = parseFloat(0).toFixed(2)
        }

        // Final Final Ace Sell Price
        acesellprice = parseFloat(Ext.getCmp('acesellprice').getRawValue()).toFixed(3);
        acesellpricefloat = parseFloat(acesellprice);
        premiumfeefloat = parseFloat(premiumfeeraw);

        finalacesellprice = parseFloat(acesellpricefloat + premiumfeefloat).toFixed(3);
        /*-------------------- Math ---------------------- */

        // Total Value inserted
        // Find Weight
        //totalestvalue = 100
        //finalbuyprice = 256.55
        totalestvalue = 0;
        // How to get final buy price?
        // Ace buy price - refinery fee = Final buy price 

        // Est value / final Sell price = xau weight
        // When total xau weight is 0, means it is value
        if (totalxauweight == 0) {
            finaltotalxauweight = parseFloat(totalvalue / finalacesellprice).toFixed(3);
            totalestvalue = parseFloat(totalvalue).toFixed(2);
            finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
        }

        // Xau Weight inserted
        // Find Total Est Value
        // Ace buy price - refinery fee = final buy price

        // Final buy price  * xau weight = total est value
        if (totalestvalue == 0) {
            totalestvalue = parseFloat(finalacesellprice * totalxauweight).toFixed(2);
            finaltotalxauweight = totalxauweight;
        }
        // Acquire Refinery fee
        premiumfee = '<span style="color:#404040;font: 900 26px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">' + premiumfeeraw + '</span>';

        //var aa= Ext.JSON.encode(params);
        //alert(aa);

        // Ui height
        var height = Ext.getBody().getViewSize().height;

        var cardHeight = height * 0.8;

        var boxHeight = height * 0.2;
        
        // Panel for Total Value
        var spotpanelselltotalvalue = new Ext.form.Panel({
            frame: true,
            layout: {
                type: 'vbox',
                fullscreen: true,
                align: 'fit',
            },
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            maxHeight: cardHeight,
            items: [
                //{ xtype: 'displayfield', flex: 1},
                {
                    //flex: 4,
                    items: [
                        //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        {

                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },
                            //flex: 6,

                            //bodyPadding: 10,

                            defaults: {
                                frame: false,
                            },

                            items: [
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">Product </span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame: false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    //flex: 3,
                                    margin: '0 0 10 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name: 'Product', reference: 'productprice', value: productspotvalue.getRawValue(),
                                        //fieldStyle: 'text-align:center;padding-left:12px;padding-top:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', 
                                        //flex: 1,
                                        renderer: function (value) {
                                            this.setHtml('<span style="text-align:center;padding-left:12px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;">' + value + '</span>')
                                        }
                                    }]

                                },
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Value (RM)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    //flex: 3,
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'Value', reference: 'value', value: totalvalue,
                                        //fieldStyle: 'padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
                                        renderer: function (value) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">' + value + '</span>')
                                        }
                                    },]

                                },
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">Premium Fee</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame: false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    //flex: 3,
                                    margin: '0 0 10 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name: 'premiumfee', reference: 'premiumfee', value: premiumfee,
                                        //fieldStyle: 'text-align:center;padding-left:12px;padding-top:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', 
                                        //flex: 1,
                                        renderer: function (value) {
                                            this.setHtml('<span style="text-align:center;padding-left:12px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;">' + value + '</span>')
                                        }
                                    }]

                                },
                            ]
                        },
                    ]
                },
                {
                    flex: 4,
                    items: [
                        //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        {

                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },
                            //flex: 6,

                            //bodyPadding: 10,

                            defaults: {
                                frame: false,
                            },

                            items: [
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE SELL (RM)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame: false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    //flex: 3,
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield',
                                        //fieldLabel: 'ACE SELL',
                                        //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                        //flex: 9,
                                        //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                        //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                        style: 'text-align:center;box-sizing:border-box;border-radius:12px;',
                                        id: 'spotordersellconfirmationval',
                                        value: aceselldesignconfirmation,
                                        name: 'acesell',
                                        maxHeight: boxHeight,
                                        renderer: function (html) {
                                            this.setHtml(html)
                                        }

                                    }]

                                },
                                /* { xtype: 'displayfield',
                                //fieldLabel: 'ACE SELL',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                id: 'spotordersellconfirmationval',
                                value: aceselldesignconfirmation,
                                name: 'acesell',
                                renderer:function(html){
                                    this.setHtml(html)
                                }
                                
                                }, */
                                // {
                                //     //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                //     //html: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">REFINING FEE</span>',
                                //     frame: false,
                                //     //style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                //     //title: 'Ask',
                                //     //flex: 3,
                                //     margin: '0 0 0 0',
                                //     items: [
                                //         {
                                //         xtype: 'displayfield',
                                //         width: '99%',
                                //         padding: '0 1 0 1',
                                //         html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 10px; font-size: 16px;color:#404040;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>PREMIUM FEE</span></h5>",
                                //         //html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>REFINING FEE</span></h5>",
                                //         //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",

                                //     },
                                //      {
                                //         //xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: '', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                //         xtype: 'displayfield', name: 'premiunfee', reference: 'premiumfee', value: premiumfee,
                                //         fieldLabel: 'Premium Fee',  
                                //         //style: 'padding-left: 10px;padding-right: 20px;',
                                //         //fieldStyle: " background-color: #ffffff ",
                                //         renderer: function (value) {
                                //             this.setHtml('<span style="font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block">' + value + '</span>')
                                //         }
                                //     },]
                                // },
                                /*
                                {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"></span>',
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
                                    margin: '0 10 0 0',
                                    items: [{
                                        xtype: 'displayfield', name:'premiumfee', reference: 'premiumfee', value: premiumfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Premium Fee', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },*/
                            ]
                        },
                    ]
                },
            ],
        });

        // Spot Panel for Total Xau Weight 
        var spotpanelselltotalxauweight = new Ext.form.Panel({
            frame: true,
            layout: {
                type: 'vbox',
                fullscreen: true,
                align: 'fit',
            },
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            maxHeight: cardHeight,
            items: [
                //{ xtype: 'displayfield', flex: 1},
                {                    
                    items: [
                        //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        {

                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },                            

                            //bodyPadding: 10,

                            defaults: {
                                frame: false,
                            },

                            items: [
                                {
                                    xtype:'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',                                    
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name: 'Product', reference: 'productprice', value: productspotvalue.rawValue, 
                                        renderer: function (html) {
                                            this.setHtml('<span style="text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;">'+html+'</span>')
                                        }
                                    },]
                                }, {
                                    html: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',

                                },
                                {
                                    xtype:'panel',   
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },                                 
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Total XAU Weight (gram)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'Value', reference: 'value', value: totalxauweight,
                                        renderer: function (html) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">'+html+'</span>')
                                        }
                                    },]
                                },
                                {
                                    xtype:'panel',   
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },                                 
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Premium Fee</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    frame: true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items: [{
                                        xtype: 'displayfield', name: 'premiumfee', reference: 'premiumfee', value: premiumfee,
                                        renderer: function (html) {
                                            this.setHtml('<span style="padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;">'+html+'</span>')
                                        }
                                    },]
                                },
                            ]
                        },
                    ]
                },
                {
                    items: [
                        //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        {

                            layout: {
                                type: 'vbox',
                                pack: 'start',
                                align: 'stretch'
                            },                            

                            //bodyPadding: 10,

                            defaults: {
                                frame: false,
                            },

                            items: [
                                {
                                    xtype: 'panel',
                                    layout: {
                                        type: 'hbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE SELL (RM)</span>',
                                    header: {
                                        style: {
                                            // backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',

                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame: false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    margin: '0 0 0 0',
                                    userCls: 'panel-headerstyle',
                                    items:[
                                        {
                                            xtype: 'displayfield',
                                            //fieldLabel: 'ACE SELL',
                                            //style="border-style:dotted;border-color:1px solid #E3EFF4"                                            
                                            //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                            //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                            style: 'text-align:center;box-sizing:border-box;border-radius:12px;',
                                            id: 'spotordersellconfirmationxau',
                                            value: aceselldesignconfirmation,
                                            name: 'acesell',
                                            maxHeight: boxHeight,
                                            renderer: function (html) {
                                                this.setHtml(html)
                                            }
                                        }
                                    ]

                                },
                                // {
                                //     //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',

                                //     frame: false,
                                //     //style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                //     //title: 'Ask',                                    
                                //     margin: '0 10 0 0',
                                //     items: [{
                                //         xtype: 'displayfield',
                                //         width: '99%',
                                //         padding: '0 1 0 1',
                                //         html: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">PREMIUM FEE</span>',
                                //         //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",

                                //     }, {
                                //         //xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: '', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                //         xtype: 'displayfield', name: 'premiunfee', reference: 'premiumfee', value: premiumfee, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Premium Fee', flex: 1, style: 'padding-left: 10px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ", renderer: function (html) {
                                //             this.setHtml(html)
                                //         }
                                //     },]
                                // },
                            ]
                        },
                    ]
                },
            ],
        });
        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: 'Your request completed successfully.',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {
                    xtype:'panel',
                    title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1>',
                    header: {
                        style: {
                            backgroundColor: 'white',
                            display: 'inline-block',
                            color: '#000000',

                        }
                    },
                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',
                    layout:'vbox',

                    margin: '0 10 0 0',
                    items: [
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Product:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'product', reference: 'product', value:productspotvalue.rawValue, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                                
                            ]

                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Final ACE Sell Price:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'finalprice', reference: 'finalprice', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },                                
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">XAU Weight:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'xauweight', reference: 'xauweight', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },                               
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">Total est. value:</span>',
                                    style: 'margin-top:20px',
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name: 'totalestvalue', reference: 'totalestvalue', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                }                              
                            ]
                        },
                        {
                            xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: ''
                        }
                    ]
                },
            ],
            buttons: [{
                text: 'OK',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }, {
                text: 'Print PDF',
                handler: function (btn) {
                    me._printOrderPDFSpot(btn);
                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                }
            },
            closeAction: 'destroy',
            //items: spotpanelbuytotalxauweight
        });

        /*-------------------------------- Panel Graphics ----------------------------- */
        //var type=selectedRecords[0].get('type');            
        var windowforspotorderselltotal = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: '100%',
            //height: '100%',
            //maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function (btn) {
                    if (spotpanelselltotalvalue.isValid()) {
                        btn.disable();
                        spotpanelselltotalvalue.submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: {
                                hdl: 'order', action: 'doSpotOrder',
                                sellprice: Ext.getCmp('acesellprice').getRawValue(),
                                uuid: Ext.getCmp('orderuuid').getRawValue(),
                                amount: spotorder.totalvalue,
                                productitem: spotorder.product,
                                sellorbuy: 'sell',

                            },
                            waitMsg: 'Processing',
                            success: function (frm, action) { //success
                                
                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderform').reset();
                                
                                //Set values
                                // Set Final Ace Sell Price
                                // Final Final Ace Sell Price                                                               
                                //finalacesellpricefloat = parseFloat(this.params.sellprice);
                                // finalacesellpricefloat = parseFloat(Ext.getCmp('acesellprice').getRawValue());
                                // finalacesellprice = parseFloat(finalacesellpricefloat + premiumfeefloat).toFixed(3);                                
                                windowforordercomplete.items.items[0].items.items[1].items.items[1].setValue(action.return[0].price);

                                windowforordercomplete.items.items[0].items.items[2].items.items[1].setValue(action.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                // Set Total Est Value
                                windowforordercomplete.items.items[0].items.items[3].items.items[1].setValue(action.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));
                                windowforordercomplete.items.items[0].items.items[4].setValue(action.return);

                                owningWindow = btn.up('window');
                                //owningWindow.closeAction='destroy';
                                windowforordercomplete.show();

                                owningWindow.close();
                                // myView.getStore().reload();

                                //windowforordercomplete.items.items[0].items.items[1].setValue(finalacesellprice);

                                // Set Xau Weight
                                /*-------------------- Math ---------------------- */

                                // Total Value inserted
                                // Find Weight
                                //totalestvalue = 100
                                //finalbuyprice = 256.55

                                // finaltotalestvalue = 0;
                                // How to get final buy price?
                                // Ace buy price - refinery fee = Final buy price 

                                // Est value / final Sell price = xau weight
                                // When total xau weight is 0, means it is value
                                // if (totalxauweight == 0) {
                                //     finaltotalxauweight = parseFloat(totalvalue / finalacesellprice).toFixed(3);
                                //     finaltotalestvalue = parseFloat(totalvalue).toFixed(2);
                                //     finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
                                // }

                                // Xau Weight inserted
                                // Find Total Est Value
                                // Ace buy price - refinery fee = final buy price

                                // Final buy price  * xau weight = total est value
                                // if (totalestvalue == 0) {
                                //     finaltotalestvalue = parseFloat(finalacesellprice * totalxauweight).toFixed(2);
                                //     finaltotalxauweight = totalxauweight;
                                // }

                             
                            },
                            failure: function (frm, action) {
                                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                btn.enable();
                                var errmsg = action.result.action.result.errorMessage;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    errmsg = action.result.errorMessage;
                                }
                                //Ext.Msg.alert('Error Message', errmsg, Ext.emptyFn);
                            }
                        });
                    } else {
                        Ext.Msg.alert('Error Message', 'All fields are required', Ext.emptyFn);
                    }
                }
            }, {
                text: 'Close',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();

                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                }
            },
            closeAction: 'destroy',
            items: spotpanelselltotalvalue
        });

        //var type=selectedRecords[0].get('type');            
        var windowforspotordersellxau = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function (btn) {
                    if (spotpanelselltotalxauweight.isValid()) {
                        btn.disable();
                        spotpanelselltotalxauweight.submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: {
                                hdl: 'order', action: 'doSpotOrder',
                                sellprice: Ext.getCmp('acesellprice').getRawValue(),
                                uuid: Ext.getCmp('orderuuid').getRawValue(),
                                weight: spotorder.totalxauweight,
                                productitem: spotorder.product,
                                sellorbuy: 'sell',
                            },
                            waitMsg: 'Processing',
                            success: function (frm, action) { //success                                   

                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderform').reset();

                                //Set values
                                // Set Final Ace Sell Price
                                // Final Final Ace Sell Price
                                //finalacesellpricefloat = parseFloat(this.params.sellprice);
                                // finalacesellpricefloat = parseFloat(Ext.getCmp('acesellprice').getRawValue());
                                // finalacesellprice = parseFloat(finalacesellpricefloat + premiumfeefloat).toFixed(3);
                                //windowforordercomplete.items.items[0].items.items[1].setValue(finalacesellprice);
                                windowforordercomplete.items.items[0].items.items[1].items.items[1].setValue(action.return[0].price);

                                windowforordercomplete.items.items[0].items.items[2].items.items[1].setValue(action.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                // Set Total Est Value
                                //windowforordercomplete.items.items[0].items.items[3].setValue(finaltotalestvalue);
                                windowforordercomplete.items.items[0].items.items[3].items.items[1].setValue(action.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));
                                windowforordercomplete.items.items[0].items.items[4].setValue(action.return);

                                owningWindow = btn.up('window');

                                windowforordercomplete.show();

                                //owningWindow.closeAction='destroy';
                                owningWindow.close();

                                // Set Xau Weight
                                /*-------------------- Math ---------------------- */

                                // Total Value inserted
                                // Find Weight
                                //totalestvalue = 100
                                //finalbuyprice = 256.55

                                // finaltotalestvalue = 0;
                                // How to get final buy price?
                                // Ace buy price - refinery fee = Final buy price 

                                // Est value / final Sell price = xau weight
                                // When total xau weight is 0, means it is value
                                // if (totalxauweight == 0) {
                                //     finaltotalxauweight = parseFloat(totalvalue / finalacesellprice).toFixed(3);
                                //     finaltotalestvalue = parseFloat(totalvalue).toFixed(2);
                                //     finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
                                // }

                                // Xau Weight inserted
                                // Find Total Est Value
                                // Ace buy price - refinery fee = final buy price

                                // Final buy price  * xau weight = total est value
                                // if (finaltotalestvalue == 0) {
                                //     finaltotalestvalue = parseFloat(finalacesellprice * totalxauweight).toFixed(2);
                                //     finaltotalxauweight = totalxauweight;
                                // }
                                //windowforordercomplete.items.items[0].items.items[2].setValue(finaltotalxauweight);
                                
                                // myView.getStore().reload();
                            },
                            failure: function (frm, action) {
                                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                btn.enable();
                                var errmsg = action.result.errorMessage;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    errmsg = action.result.errorMessage;
                                }
                                //Ext.Msg.alert('Error Message', errmsg, Ext.emptyFn);
                            }
                        });
                    } else {
                        Ext.Msg.alert('Error Message', 'All fields are required', Ext.emptyFn);
                    }
                }
            }, {
                text: 'Close',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();

                }
            }],
            listeners: {
                close: function (win) {
                    if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                        Ext.getCmp('spotorderbuyconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                        Ext.getCmp('spotorderbuyconfirmationxau').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationval') != null) {
                        Ext.getCmp('spotordersellconfirmationval').destroy();
                    }
                    if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                        Ext.getCmp('spotordersellconfirmationxau').destroy();
                    }

                }
            },
            closeAction: 'destroy',
            items: spotpanelselltotalxauweight
        });

        /* ---------------------------------- End Panel Graphics --------------------------------- */


        /* ---------------------------------- Check Permission for Spot Order Sell --------------------------------- */
        // Get Permission 
        allpermissions = vm.get('permissions');
        productpermission = allpermissions.find(x => x.id === spotorder.product);


        // Run through form and check for empty fields
        var fields = form.getFields();
        var forminput = Object.values(fields);

        // initialize variables for form condition checking
        checkvalue = 1;
        checkweight = 1;
        checkbuy = 1;
        checksell = 1;

        // weight checking
        doweightcheck = 0;
        checkweightdivisible = 0;

        // Initialize null checker
        checkvaluenull = 1;
        checkweightnull = 1;
        checkbuynull = 1;
        checksellnull = 1;

        // Set Error Messages
        /*
        errmsgvalue = 'Sorry, ACE Cannot Buy This Product By Amount';
        errmsgweight = 'Sorry, We Do Not Sell By Amount';
        errmsgbuy = 'Sorry, ACE Cannot Buy This Product By Weight';
        errmsgsell = 'Sorry, ACE Cannot Sell This Product By Weight';
        */

        validformselltotal = 0;
        validformsellweight = 0;

        // Begin Loop
        for (index = 0; index < forminput.length; index++) {
            //alert(forminput[index].value);
            // If field is not empty
            if (forminput[index].rawValue != "") {

                // Begin checking for empty fields
                // If the filled fields are the 4 inputs
                // Check id
                if (forminput[index].id == 'totalvaluespotdashboard' || forminput[index].id == 'totalxauweightspotdashboard' || forminput[index].id == 'acesellprice') {

                    // Begin checking for permission
                    // Checking if fields are filled
                    // Then save input combination for message display
                    //alert("Not EMPTY!" + forminput[index].id);
                    //console.log(Ext.getCmp('totalvaluespotdashboard').isDisabled());
                    if (forminput[index].id == 'totalvaluespotdashboard' && (Ext.getCmp('totalvaluespotdashboard').isDisabled() == null || Ext.getCmp('totalvaluespotdashboard').isDisabled() == false ) && totalvalue > 0) {

                        // Value is not null
                        checkvaluenull = 0;
                        // Check for permission
                        // Display warning if not true 
                        if (productpermission.bycurrency != true) {
                            // Set flag to indicate product has permission for value  
                            checkvalue = 0;

                        }
                        //alert("Can value " + productpermission.bycurrency);
                    }
                    //console.log(Ext.getCmp('totalxauweightspotdashboard').isDisabled());
                    if (forminput[index].id == 'totalxauweightspotdashboard' && (Ext.getCmp('totalxauweightspotdashboard').isDisabled() == null || Ext.getCmp('totalxauweightspotdashboard').isDisabled() == false) && totalxauweight > 0) {
                        //console.log(productpermission.weight);
                        // Checking if fields are filled
                        checkweightnull = 0;
                        // Check if weight is within reasonable range

                        if (productpermission.weight != null) {
                            //debugger;
                            // Ignore weights that are 0
                            if (productpermission.weight != 0) {
                                // Check if modulus is 0
                                // Enable weight checking
                                doweightcheck = 1;
                                //debugger;
                                if ((forminput[index].rawValue % productpermission.weight) != 0) {
                                    checkweightdivisible = 0;
                                } else {
                                    checkweightdivisible = 1;
                                }
                                //debugger;
                                //alert("this not 0, cant be divided");
                            }
                        }
                        //forminput[index].value/
                        if (productpermission.byweight != true) {
                            // Set flag to indicate product has permission for value     
                            checkweight = 0;

                        }
                        //alert("Can weight" + productpermission.byweight);
                    }
                    if (forminput[index].id == 'acesellprice' && (Ext.getCmp('acesellprice').isDisabled() == null || Ext.getCmp('acesellprice').isDisabled() == false)) {

                        // Checking if fields are filled
                        checksellnull = 0;

                        if (productpermission.cansell != true || productpermission.partnerCanBuy != true) {
                            // Set flag to indicate product has permission for Sell
                            checksell = 0;

                        }
                        //alert("Can sell " + productpermission.cansell);
                    }
                    // End Checking for permission

                } else {
                    //Ext.getCmp(itemname).setHidden(true);
                } // End Checking for empty fields

            } // End Loop
        }    

        if (doweightcheck == 1) {
            if (checkweightnull == 0 && checksellnull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checksell == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checksell == 1 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checksell == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = yes
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }


                } else if (checkweight == 1 && checksell == 0 && checkweightdivisible == 0) {

                    // Weight = yes
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checksell == 0 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 1 && checksell == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = no
                    Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);

                } else if (checkweight == 1 && checksell == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validformsellweight = 1;
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        } else {
            // Check permission and display accordingly           
            if (checkvaluenull == 0 && checksellnull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkvalue == 0 && checksell == 1) {

                    // Value = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);

                } else if (checkvalue == 1 && checksell == 0) {

                    // Value = yes
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkvalue == 0 && checksell == 0) {

                    // Value = no
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validformselltotal = 1;
                }


            } else if (checkweightnull == 0 && checksellnull == 0) {
                
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checksell == 1) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, ACE Cannot Sell This Product By Weight', Ext.emptyFn);

                } else if (checkweight == 1 && checksell == 0) {

                    // Weight = yes
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }
                    

                } else if (checkweight == 0 && checksell == 0) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.cansell != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanBuy != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validformsellweight = 1;
                    
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        }

        /* ----------------------------------  End Check Permission for Spot Order Sell --------------------------------- */

        ///////////////////////////////////////////////////////////////////  
        
        
        if (totalvalue != null && totalxauweight == 0 && product != null && validformselltotal == 1) {

            windowforspotorderselltotal.show();


       } else if (totalvalue == 0 && totalxauweight != null && product != null && validformsellweight == 1) {       

            windowforspotordersellxau.show();


        } else {
            /*
           Ext.Msg.show({
               title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
               msg: 'Please fill all required fields'});
           */
            if (Ext.getCmp('spotorderbuyconfirmationval') != null) {
                Ext.getCmp('spotorderbuyconfirmationval').destroy();
            }
            if (Ext.getCmp('spotorderbuyconfirmationxau') != null) {
                Ext.getCmp('spotorderbuyconfirmationxau').destroy();
            }
            if (Ext.getCmp('spotordersellconfirmationval') != null) {
                Ext.getCmp('spotordersellconfirmationval').destroy();
            }
            if (Ext.getCmp('spotordersellconfirmationxau') != null) {
                Ext.getCmp('spotordersellconfirmationxau').destroy();
            }
        }
    },

    spotOrderAction: function (btn) {
        if (btn.id == 'spotacebuy') {
            //Ext.getCmp('sellorbuy').setValue('sell');
        } else if (btn.id == 'spotacesell') {
            //Ext.getCmp('sellorbuy').setValue('buy');
        }
        var view = this.getView(),
            model = Ext.create('snap.view.spotorder.FormModel', view.getValues());
        var errors = { productitem: true, amount: true, weight: true, id: true, uuid: true };
        if (view.getValues().uuid == null) {
            errors.uuid = "Sorry Your Order Cannot Be Process, Our ACE Connection Currently Offline";
            Ext.toast(errors.uuid, 4000);
        }
        if (view.getValues().productitem == null) {
            errors.productitem = "Product is required";
        }
        if (view.getValues().amount == "" && view.getValues().weight == "") {
            errors.amount = "This field is required";
        }
        var regex = /^[0-9]*\.?[0-9]*$/;
        if (view.getValues().amount != "" && !regex.test(view.getValues().amount)) {
            errors.amount = "Enter valid number";
        }
        var validationerror = 0;
        for (var err in errors) {
            if (errors[err] != true) {
                validationerror++;
            }
        }
        if (validationerror != 0) {
            //var errors = model.getValidation().getData();
            Object.keys(errors).forEach(function (f) {
                //console.log(view);
                var field = view.getFields(f);
                if (field && errors[f] !== true) {
                    field.markInvalid(errors[f]);
                }
            });
            return false;
        }
        var form = this.getView();
        form.submit({
            submitEmptyText: false,
            url: 'index.php',
            method: 'POST',
            params: { hdl: 'spotorder', action: 'makeOrder' },
            waitMsg: 'Processing',
            success: function (frm, action) { //success
                Ext.Msg.alert('Success', 'Submitted Successfully !', Ext.emptyFn);
            },
            failure: function (frm, action) {
                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
            }
        });
    },

    doFutureOrderQueue: function (elemnt) {

        var me = this;
        //alert("test");
        //var form = elemnt.lookupController().lookupReference('futureorder-form');
        var form = elemnt.up('formpanel');

        if (!form.isValid()) {
            Ext.Msg.alert('Error Message', 'Some field value is invalid', Ext.emptyFn);
            return;
        }

        // Do init for future order
        // Start Placeholder
        feename = "Additional Fee";
        acematchprice = 0;
        finalprice= 0;
        acematchpricelabel = "Ace Matching Price";
        finalpricelabel = "Final Price";
        // End Placeholder

        // Create forms
        futureorder = form.getValues();
        //futureorder = form2.getFieldValues();
        productfuturevalue = Ext.getCmp('productfuture');

        // Total value to decimal 
        // Check Total Value
        if (futureorder.totalvalue != null && futureorder.totalvalue != '') {
            totalvalue = parseFloat(futureorder.totalvalue).toFixed(2);
        } else {

            totalvalue = parseFloat(0).toFixed(2);
        }

        // Check total xau weight
        if (futureorder.totalxauweight != null && futureorder.totalxauweight != '') {
            totalxauweight = parseFloat(futureorder.totalxauweight).toFixed(3);
        } else {
            totalxauweight = 0;
        }

        // Check if product is not selected 
        if (futureorder.product == null) {
            Ext.Msg.alert('Error Message', 'Product field is required', Ext.emptyFn);
        }

        orderfees = vm.get('fees');

        fee = orderfees.find(x => x.id === futureorder.product);

        // Set Refinery Fee (temp)
        if (fee.refineryfee != null && fee.refineryfee != '') {
            refineryfee = parseFloat(fee.refineryfee).toFixed(2);
        } else {
            refineryfee = parseFloat(0).toFixed(2);
        }

        // Set Premium Fee (temp)
        if (fee.premiumfee != null && fee.premiumfee != '') {
            premiumfee = parseFloat(fee.premiumfee).toFixed(2);
        } else {
            premiumfee = parseFloat(0).toFixed(2);
        }
        // Set Refinery Fee (temp)

        //debugger;
        // Set Buy Prices
        // Check Ace Buy Price
        if (futureorder.acebuyprice != null && futureorder.acebuyprice != '') {
            acebuyprice = parseFloat(futureorder.acebuyprice).toFixed(3);
            // Add match price
            acematchprice = acebuyprice;
            finalacebuyprice = parseFloat(acebuyprice - refineryfee).toFixed(3);
            finalprice = finalacebuyprice;
            fee = refineryfee.toString();
            feename = "Refinery Fee";
            acematchpricelabel = "Ace Buy Matching Price (RM/g):";
            finalpricelabel = "Final Buy Price";
        } else {
            acebuyprice = 0;
        }
        // Check Ace Sell Price
        if (futureorder.acesellprice != null && futureorder.acesellprice != '') {
            acesellprice = parseFloat(futureorder.acesellprice).toFixed(3);
            // Add match price
            acematchprice = acesellprice;
            acesellpricefloat = parseFloat(acesellprice);
            premiumfeefloat = parseFloat(premiumfee);

            finalacesellprice = parseFloat(acesellpricefloat + premiumfeefloat).toFixed(3);

            //Ext.getCmp('specialfee').setBoxLabel('Premium Fee');
            finalprice = finalacesellprice;
            fee = premiumfee.toString();
            feename = 'Premium Fee';
            acematchpricelabel = "Ace Sell Matching Price (RM/g):";
            finalpricelabel = "Final Sell Price";
        } else {
            acesellprice = 0;
        }




        // Math if necessary 
        /*-------------------- Math ---------------------- */

        // Total Value inserted
        // Find Weight
        //totalestvalue = 100
        //finalbuyprice = 256.55

        totalestvalue = 0;
        // How to get final buy price?
        // Ace buy price - refinery fee = Final buy price 

        // Est value / final buy price = xau weight
        // When total xau weight is 0, means it is value
        if (totalxauweight == 0) {
            totalxauweight = parseFloat(totalvalue / finalprice).toFixed(3);
            totalestvalue = parseFloat(totalvalue).toFixed(3);
        }

        // Xau Weight inserted
        // Find Total Est Value
        // Ace buy price - refinery fee = final buy price

        // Final buy price  * xau weight = total est value
        if (totalestvalue == 0) {
            totalestvalue = parseFloat(finalprice * totalxauweight).toFixed(3);
        }

        // End math


        // Spot Panel for Total Xau Weight 
        var futurepanel = new Ext.form.Panel({
            frame: true,
            layout: 'vbox',
            reference: 'futureorder-confirmation',
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            listeners: {

                beforerender: function (cmp) {



                    var innerform = cmp.down().down().items;
                    //innerform.items[4].setHidden(true)

                    var fields = form.getFields();
                    var input = Object.values(fields);
                    for (index = 0; index < input.length; index++) {
                        if (input.items[index].value == "") {

                            //(input.items[index].id);
                            itemname = input.items[index].id + "display";

                            // One without any display / hiding displays
                            if (input.items[index].id == 'totalvaluefuturedashboard' || input.items[index].id == 'totalxauweightfuturedashboard') {

                            } else {
                                Ext.getCmp(itemname).setHidden(true);
                            }


                        }
                    }


                }
            },
            items: [
                {
                    xtype: 'fieldcontainer',
                    //fieldLabel: 'Limits',
                    //defaultType: 'textfield',
                    layout: 'hbox',                    
                    items: [
                        {
                            xtype: 'fieldcontainer',
                            layout: 'vbox',                            
                            items: [
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html:'Product:',
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },{
                                            xtype: 'displayfield', id: 'productfuturedisplay', name: 'product', reference: 'product', value: productfuturevalue.getRawValue(), labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                                        }
                                    ]
                                },
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html: acematchpricelabel,
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },{
                                            xtype: 'displayfield', name: 'acematchprice', reference: 'acematchprice', value: acematchprice, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',  style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                                        },
                                    ]
                                },
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html:feename,
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },
                                        {
                                            xtype: 'displayfield', id: 'specialfee', name: 'refiningfee', reference: 'refiningfee', value: fee, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif; font-size:120%; font-weight:bold;',  style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff; font-weight:bold; font-size:200%; ",
                                        },
                                    ]
                                },
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html:'Total Est. Value',
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },{
                                            xtype: 'displayfield', name: 'totalestvalue', reference: 'totalestvalue', value: totalestvalue, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #fff ",
                                        },
                                    ]
                                },
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html:'Xau Weight',
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },{
                                            xtype: 'displayfield', id: 'totalxauweightfuturedashboarddisplay', name: 'xauweight', reference: 'xtotalxauweightfuturedashboard', value: totalxauweight, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',  style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                                        },
                                    ]
                                },
                                // {
                                //     xtype:'panel',
                                //     layout:'hbox',
                                //     items:[
                                //         {
                                //             xtype:'label',
                                //             html:'ACE Sell Matching Price (RM/g)',
                                //             style: {
                                //                 'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                //                 'margin':'5px 0px',
                                //                 'width':'50%'
                                //             }
                                //         },{
                                //             xtype: 'displayfield', id: 'acesellpricefuturedashboarddisplay', name: 'acesellprice', reference: 'acesellprice', value: acesellprice, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #fff ",
                                //         },
                                //     ]
                                // },
                                {
                                    xtype:'panel',
                                    layout:'hbox',
                                    items:[
                                        {
                                            xtype:'label',
                                            html:finalpricelabel,
                                            style: {
                                                'font': '900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                                'margin':'5px 0px',
                                                'width':'50%'
                                            }
                                        },{
                                            xtype: 'displayfield', name: 'finalprice', reference: 'finalprice', value: finalprice, labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',  style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #fff ",
                                        },
                                    ]
                                }                               

                            ]
                        },                       

                    ]
                    
                },                
            ]
        });

        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: '3',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {
                    html: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1ssssss>',
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
                    margin: '0 10 0 0',
                    items: [{
                        xtype: 'displayfield', name: 'product', reference: 'product', value: '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + productfuturevalue.rawValue + '</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Product', flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name: 'xauweight', reference: 'xauweight', value: '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + totalxauweight + '</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'XAU Weight', flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name: 'acematchprice', reference: 'acematchprice', value: '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + acematchprice + '</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: acematchpricelabel, flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name: 'finalprice', reference: 'finalprice', value: '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + finalprice + '</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: finalpricelabel, flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name: 'totalestvalue', reference: 'totalestvalue', value: '<span style="color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;">' + totalestvalue + '</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Total est. value', flex: 1, style: 'padding-left: 20px;padding-right: 20px;', fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: '', 
                    }]
                },
            ],
            buttons: [{
                text: 'OK',
                handler: function (btn) {

                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }, {
                text: 'Print PDF',
                handler: function (btn) {
                    //me._printOrderPDFFuture(btn);
                }
            }],
            closeAction: 'destroy',
            //items: spotpanelbuytotalxauweight
        });
        // End Order Window

        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: 'Your request completed successfully.',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {   
                    xtype:'panel',
                    titile: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1ssssss>',
                    header: {
                        style: {
                            backgroundColor: 'white',
                            display: 'inline-block',
                            color: '#000000',
                            
                        }
                    },
                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                    //title: 'Ask',                   
                    margin: '0 10 0 0',
                    items: [
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'Product:',
                                    style:{
                                        'margin-top':'20px',
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name:'product', reference: 'product', value:productfuturevalue.rawValue, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em',
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },   
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'XAU Weight:',
                                    style:{
                                        'margin-top':'20px',
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name:'xauweight', reference: 'xauweight', value: totalxauweight, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em',
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },  
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:acematchpricelabel,
                                    style:{
                                        'margin-top':'20px',
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name:'acematchprice', reference: 'acematchprice', value: acematchprice, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em',
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:finalpricelabel,
                                    style:{
                                        'margin-top':'20px',
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name:'finalprice', reference: 'finalprice', value:  finalprice , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em',
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                            ]
                        },
                        {
                            xtype:'panel',
                            layout:'hbox',
                            items:[
                                {
                                    xtype:'label',
                                    html:'Total est. value:',
                                    style:{
                                        'margin-top':'20px',
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em'
                                    },
                                    width:'50%'
                                },
                                {
                                    xtype: 'displayfield', name:'totalestvalue', reference: 'totalestvalue', value: totalestvalue , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block',style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    style:{
                                        'color':'#404040',
                                        'font': '900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        'background-color':'#ffffff',
                                        'border-radius':'40px',
                                        'padding': '0.5em',
                                    },
                                    renderer: function (html) {
                                        this.setHtml(html)
                                    }
                                },
                            ]
                        }, 
                        {
                            xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: ''
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
            },{
                text: 'Print PDF',
                handler: function(btn) {
                    me._printOrderPDFFuture(btn);                    
                }
            }],
            closeAction: 'destroy',
            //items: spotpanelbuytotalxauweight
        });
        // End Order Window

        var windowforfutureorder = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: '100%',
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function (btn) {
                    if (futurepanel.isValid()) {
                        btn.disable();
                        futurepanel.submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: {
                                hdl: 'order', action: 'doFutureOrder',
                                fobuyprice: futureorder.acebuyprice,
                                fosellprice: futureorder.acesellprice,
                                uuid: futureorder.uuid,
                                foamount: futureorder.totalvalue,
                                foweight: futureorder.totalxauweight,
                                foproductitem: futureorder.product,
                            },
                            waitMsg: 'Processing',
                            success: function (frm, action) { //success                                   
                                windowforordercomplete.show();
                                windowforordercomplete.items.items[0].items.items[5].setValue(action.return);
                                owningWindow = btn.up('window');
                                //owningWindow.closeAction='destroy';
                                owningWindow.close();
                                myView.getStore().reload();
                            },
                            failure: function (frm, action) {
                                Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                btn.enable();
                                var errmsg = action.result.errorMessage;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    //windowforordercomplete.show();
                                    errmsg = action.result.errorMessage;
                                }
                                //Ext.Msg.alert('Error Message', errmsg, Ext.emptyFn);
                            }
                        });
                    } else {
                        Ext.Msg.alert('Error Message', 'All fields are required', Ext.emptyFn);
                    }
                }
            }, {
                text: 'Close',
                handler: function (btn) {
                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }],
            listeners: {
                close: function (win) {
                    /*
                    // Reenable hidden components
                    Ext.getCmp('productfuturedisplay').setHidden(false);
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acebuypricefuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acesellpricefuturedashboarddisplay').setHidden(false);
                    
                    // Clear cmp
                    Ext.getCmp('productfuturedisplay').destroy();
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').destroy();
                    Ext.getCmp('acebuypricefuturedashboarddisplay').destroy();
                    Ext.getCmp('acesellpricefuturedashboarddisplay').destroy();
                    */
                    if (Ext.getCmp('productfuturedisplay') != null) {
                        Ext.getCmp('productfuturedisplay').destroy();
                    }
                    if (Ext.getCmp('acebuypricefuturedashboarddisplay') != null) {
                        Ext.getCmp('acebuypricefuturedashboarddisplay').destroy();
                    }
                    if (Ext.getCmp('specialfee') != null) {
                        Ext.getCmp('specialfee').destroy();
                    }
                    if (Ext.getCmp('totalestvalue') != null) {
                        Ext.getCmp('totalestvalue').destroy();
                    }
                }
            },
            closeAction: 'destroy',
            items: futurepanel
        });


        // Get Permission 
        allpermissions = vm.get('permissions');
        productpermission = allpermissions.find(x => x.id === futureorder.product);


        // Run through form and check for empty fields
        var fields = form.getFields();
        var forminput = Object.values(fields);

        // initialize variables for form condition checking
        checkvalue = 1;
        checkweight = 1;
        checkbuy = 1;
        checksell = 1;

        // weight checking
        doweightcheck = 0;
        checkweightdivisible = 0;

        // Initialize null checker
        checkvaluenull = 1;
        checkweightnull = 1;
        checkbuynull = 1;
        checksellnull = 1;

        // Set Error Messages
        /*
        errmsgvalue = 'Sorry, ACE Cannot Buy This Product By Amount';
        errmsgweight = 'Sorry, We Do Not Sell By Amount';
        errmsgbuy = 'Sorry, ACE Cannot Buy This Product By Weight';
        errmsgsell = 'Sorry, ACE Cannot Sell This Product By Weight';
        */

        validform = 0;

        // Begin Loop
        for (index = 0; index < forminput.length; index++) {
            //alert(forminput[index].value);
            // If field is not empty
            if (forminput[index].rawValue != "") {

                // Begin checking for empty fields
                // If the filled fields are the 4 inputs
                // Check id
                if (forminput[index].id == 'productfuture' || forminput[index].id == 'totalvaluefuturedashboard' || forminput[index].id == 'totalxauweightfuturedashboard' || forminput[index].id == 'acebuypricefuturedashboard' || forminput[index].id == 'acesellpricefuturedashboard') {

                    // Begin checking for permission
                    // Checking if fields are filled
                    // Then save input combination for message display
                    //alert("Not EMPTY!" + forminput[index].id);
                    if (forminput[index].id == 'totalvaluefuturedashboard' && (Ext.getCmp('totalvaluefuturedashboard').isDisabled() == null || Ext.getCmp('totalvaluefuturedashboard').isDisabled() == false) && form.getValues().totalvalue > 0) {

                        // Value is not null
                        checkvaluenull = 0;
                        // Check for permission
                        // Display warning if not true 
                        if (productpermission.bycurrency != true) {
                            // Set flag to indicate product has permission for value  
                            checkvalue = 0;

                        }
                        //alert("Can value " + productpermission.bycurrency);
                    }
                    if (forminput[index].id == 'totalxauweightfuturedashboard' && (Ext.getCmp('totalxauweightfuturedashboard').isDisabled() == null || Ext.getCmp('totalxauweightfuturedashboard').isDisabled() == false) && form.getValues().totalxauweight > 0) {

                        // Checking if fields are filled
                        checkweightnull = 0;
                        // Check if weight is within reasonable range

                        if (productpermission.weight != null) {
                            //debugger;
                            // Ignore weights that are 0
                            if (productpermission.weight != 0) {
                                // Check if modulus is 0
                                // Enable weight checking
                                doweightcheck = 1;
                                //debugger;
                                if ((forminput[index].rawValue % productpermission.weight) != 0) {
                                    checkweightdivisible = 0;
                                } else {
                                    checkweightdivisible = 1;
                                }
                                //debugger;
                                //alert("this not 0, cant be divided");
                            }
                        }
                        //forminput[index].value/
                        if (productpermission.byweight != true) {
                            // Set flag to indicate product has permission for value     
                            checkweight = 0;

                        }
                        //alert("Can weight" + productpermission.byweight);
                    }
                    if (forminput[index].id == 'acebuypricefuturedashboard' && (Ext.getCmp('acebuypricefuturedashboard').isDisabled() == null || Ext.getCmp('acebuypricefuturedashboard').isDisabled() == false) && form.getValues().acebuyprice > 0) {

                        // Checking if fields are filled
                        checkbuynull = 0;

                        if (productpermission.canbuy != true || productpermission.partnerCanSell != true) {
                            // Set flag to indicate product has permission for Buy
                            checkbuy = 0;

                        }
                        //lert("Can buy" + productpermission.canbuy);
                    }
                    if (forminput[index].id == 'acesellpricefuturedashboard' && (Ext.getCmp('acesellpricefuturedashboard').isDisabled() == null || Ext.getCmp('acesellpricefuturedashboard').isDisabled() == false) && form.getValues().acesellprice > 0) {

                        // Checking if fields are filled
                        checksellnull = 0;

                        if (productpermission.cansell != true || productpermission.partnerCanBuy != true) {
                            // Set flag to indicate product has permission for Sell
                            checksell = 0;

                        }
                        //alert("Can sell " + productpermission.cansell);
                    }
                    // End Checking for permission

                } else {
                    //Ext.getCmp(itemname).setHidden(true);
                } // End Checking for empty fields

            } // End Loop
        }

        // Check if there is weight check
        if (doweightcheck == 1) {
            if (checkweightnull == 0 && checkbuynull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checkbuy == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Buy = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checkbuy == 1 && checkweightdivisible == 1) {

                    // Weight = no
                    // Buy = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checkbuy == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Buy = yes
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                }else if (checkweight == 1 && checkbuy == 0 && checkweightdivisible == 0) {

                    // Weight = yes
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checkbuy == 0 && checkweightdivisible == 0) {

                    // Weight = no
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);
                    }

                } else if (checkweight == 1 && checkbuy == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Buy = no
                    Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);

                } else if (checkweight == 1 && checkbuy == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }

            } else if (checkweightnull == 0 && checksellnull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checksell == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checksell == 1 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 0 && checksell == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = yes
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 1 && checksell == 0 && checkweightdivisible == 0) {

                    // Weight = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checksell == 0 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);
                    }

                } else if (checkweight == 1 && checksell == 1 && checkweightdivisible == 0) {

                    // Weight = no
                    // Sell = no
                    Ext.Msg.alert('Alert', 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(0), Ext.emptyFn);

                } else if (checkweight == 1 && checksell == 0 && checkweightdivisible == 1) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        } else {
            // Check permission and display accordingly
            if (checkvaluenull == 0 && checkbuynull == 0) {

                // Check if the form fields have the corresponding permissions
                if (checkvalue == 0 && checkbuy == 1) {

                    // Value = no
                    // Buy = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);

                } else if (checkvalue == 1 && checkbuy == 0) {

                    // Value = yes
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else if (checkvalue == 0 && checkbuy == 0) {

                    // Value = no
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }



            } else if (checkvaluenull == 0 && checksellnull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkvalue == 0 && checksell == 1) {

                    // Value = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);

                } else if (checkvalue == 1 && checksell == 0) {

                    // Value = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else if (checkvalue == 0 && checksell == 0) {

                    // Value = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Currency', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }


            } else if (checkweightnull == 0 && checkbuynull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checkbuy == 1) {

                    // Weight = no
                    // Buy = yes
                    Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);

                } else if (checkweight == 1 && checkbuy == 0) {

                    // Weight = yes
                    // Buy = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checkbuy == 0) {

                    // Weight = no
                    // Buy = noE
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Buy This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Sell This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }

            } else if (checkweightnull == 0 && checksellnull == 0) {
                // Check if the form fields have the corresponding permissions
                if (checkweight == 0 && checksell == 1) {

                    // Weight = no
                    // Sell = yes
                    Ext.Msg.alert('Alert', 'Sorry, ACE Cannot Sell This Product By Weight', Ext.emptyFn);

                } else if (checkweight == 1 && checksell == 0) {

                    // Weight = yes
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else if (checkweight == 0 && checksell == 0) {

                    // Weight = no
                    // Sell = no
                    if(productpermission.canbuy != true ){
                        Ext.Msg.alert('Alert', 'Sorry, Ace Do Not Sell This Product', Ext.emptyFn);
                    }else if (productpermission.partnerCanSell != true){
                        Ext.Msg.alert('Alert', 'Sorry, You Are Not Allowed To Buy This Product.', Ext.emptyFn);
                    }else{
                        Ext.Msg.alert('Alert', 'Sorry, This Product Cannot Be Transacted By Weight', Ext.emptyFn);
                    }

                } else {
                    validform = 1;
                }

            } else {
                Ext.Msg.alert('Alert', 'Please fill mandatory fields', Ext.emptyFn);
            }
        }


        /*
        // Check permission and display accordingly
        if (checkvalue == 0 && checkbuy == 0){
            Ext.Msg.show({
                title: 'Alert',
                msg: 'Sorry, ACE Cannot Buy This Product By Amount',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
            });

            

        } else if (checkvalue == 0 && checksell == 0){
            Ext.Msg.show({
                title: 'Alert',
                msg: 'Sorry, We Do Not Sell By Amount',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
            
        } else if (checkweight == 0 && checkbuy == 0){
            Ext.Msg.show({
                title: 'Alert',
                msg: 'Sorry, ACE Cannot Buy This Product By Weight',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
            
        } else if (checkweight == 0 && checksell == 0){
            Ext.Msg.show({
                title: 'Alert',
                msg: 'Sorry, ACE Cannot Sell This Product By Weight',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
            
        }else {
            validform = 1;
        }
        */
        //debugger;

        if (validform == 1) {
            windowforfutureorder.show();
        } else {
            windowforfutureorder.destroy();
        }

        // Permission checking first level ()
        // ,, Checks for can Buy
        /*
        if(Ext.getCmp('totalvaluefuturedashboard').disabled == false) {
            alert("val online");
        }
        if(Ext.getCmp('totalxauweightfuturedashboard').disabled == false) {
            alert("weight online");
        }
        if(Ext.getCmp('acebuypricefuturedashboard').disabled == false) {
            alert("hakuna buy online");
        }
        if(Ext.getCmp('acesellpricefuturedashboard').disabled == false) {
            alert("sell online");
        }
        */

        //premiumfeeraw = parseFloat(fee.premiumfee).toFixed(2);
        // Acquire Refinery fee
        //premiumfee = '<span style="color:#404040;font: 900 26px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">' + premiumfeeraw + '</span>';
        //debugger;



        //debugger;
    },

    renderChange: function () {
        return this.renderSign(100, '0.00');
    },

    renderPercent: function (value) {
        return this.renderSign(value, '0.00%');
    },

    renderSign: function (value, format) {
        var text = Ext.util.Format.number(value, format),
            tpl = this.signTpl,
            data = this.data;

        if (Math.abs(value) > 0.1) {
            if (!tpl) {
                this.signTpl = tpl = this.getView().lookupTpl('signTpl');
                this.data = data = {};
            }

            data.value = value;
            data.text = text;

            //text = tpl.apply(data);
            text = "test";
        }

        return text;
    },
    _printOrderPDFSpotBuy: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;
        
        // Get Printable data
        /* product = btn.up().up().items.items[0].items.items[0].getValue();
        finalprice =  btn.up().up().items.items[0].items.items[1].getValue();
        xauweight =  btn.up().up().items.items[0].items.items[2].getValue();
        totalestvalue =  btn.up().up().items.items[0].items.items[3].getValue();
        orderid = btn.up().up().items.items[0].items.items[4].getValue(); */      
        product = btn.up().up().items.items[0].items.items[0].items.items[1].getValue();
        finalprice =  btn.up().up().items.items[0].items.items[1].items.items[1].getValue();
        xauweight =  btn.up().up().items.items[0].items.items[2].items.items[1].getValue();
        totalestvalue =  btn.up().up().items.items[0].items.items[3].items.items[1].getValue();
        orderid = btn.up().up().items.items[0].items.items[4].getValue();        

        var url = 'index.php?hdl=order&action=printSpotOrderBuy&product='+product+'&finalprice='+finalprice+'&xauweight='+xauweight+'&totalestvalue='+totalestvalue+'&orderid='+orderid;
				Ext.Ajax.request({
					url: url,
					method: 'get',
					waitMsg: 'Processing',
					//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
					autoAbort: false,
					success: function (result) {
						var win = window.open('');
							win.location = url;
							win.focus();
					},
					failure: function () {
						
						Ext.Msg.show({
							title: 'Error Message',
							msg: 'Failed to retrieve data',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});

    },

    _printOrderPDFSpotSell: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;
        
        // Get Printable data
        /* product = btn.up().up().items.items[0].items.items[0].getValue();
        finalprice =  btn.up().up().items.items[0].items.items[1].getValue();
        xauweight =  btn.up().up().items.items[0].items.items[2].getValue();
        totalestvalue =  btn.up().up().items.items[0].items.items[3].getValue();
        orderid = btn.up().up().items.items[0].items.items[4].getValue(); */        
        product = btn.up().up().items.items[0].items.items[0].items.items[1].getValue();
        finalprice =  btn.up().up().items.items[0].items.items[1].items.items[1].getValue();
        xauweight =  btn.up().up().items.items[0].items.items[2].items.items[1].getValue();
        totalestvalue =  btn.up().up().items.items[0].items.items[3].items.items[1].getValue();
        orderid = btn.up().up().items.items[0].items.items[4].getValue();       

        var url = 'index.php?hdl=order&action=printSpotOrderSell&product='+product+'&finalprice='+finalprice+'&xauweight='+xauweight+'&totalestvalue='+totalestvalue+'&orderid='+orderid;
				Ext.Ajax.request({
					url: url,
					method: 'get',
					waitMsg: 'Processing',
					//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
					autoAbort: false,
					success: function (result) {
						var win = window.open('');
							win.location = url;
							win.focus();
					},
					failure: function () {
						
						Ext.Msg.show({
							title: 'Error Message',
							msg: 'Failed to retrieve data',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});

    },
    
    _printOrderPDFSpot: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;
        
        // Get Printable data
        orderid = btn.up().up().items.items[0].items.items[4].getValue()[0].id;

        var url = 'index.php?hdl=order&action=printSpotOrder&orderid='+orderid;
				Ext.Ajax.request({
					url: url,
					method: 'get',
					waitMsg: 'Processing',
					//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
					autoAbort: false,
					success: function (result) {
						var win = window.open('');
							win.location = url;
							win.focus();
					},
					failure: function () {
						
						Ext.Msg.show({
							title: 'Error Message',
							msg: 'Failed to retrieve data',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});

    },
    
    _printOrderPDFFuture: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;
        
        // Get Printable data
        product = btn.up().up().items.items[0].items.items[0].items.items[1].getValue();
        xauweight =  btn.up().up().items.items[0].items.items[1].items.items[1].getValue();
        matchingprice =  btn.up().up().items.items[0].items.items[2].items.items[1].getValue();
        finalprice =  btn.up().up().items.items[0].items.items[3].items.items[1].getValue();
        totalestvalue = btn.up().up().items.items[0].items.items[4].items.items[1].getValue();

        matchingpricelabel = btn.up().up().items.items[0].items.items[2].items.items[0].getHtml();
        finallabel = btn.up().up().items.items[0].items.items[3].items.items[0].getHtml()

        orderid = btn.up().up().items.items[0].items.items[5].getValue();

        if(finallabel == "Final Buy Price"){
            buyorsell = 'buy';
        }else if(finallabel == "Final Sell Price"){
            buyorsell = 'sell';
        }else {
            Ext.Msg.show({
                title: 'Error Message',
                msg: 'Data is incomplete!',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });

            return;
        }
           
        var url = 'index.php?hdl=order&action=printFutureOrder&product='+product+'&xauweight='+xauweight+'&matchingprice='+matchingprice+'&finalprice='+finalprice+'&totalestvalue='+totalestvalue+'&buyorsell='+buyorsell+'&orderid='+orderid;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
            autoAbort: false,
            success: function (result) {
                var win = window.open('');
                    win.location = url;
                    win.focus();
            },
            failure: function () {
                
                Ext.Msg.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });

    },


});
