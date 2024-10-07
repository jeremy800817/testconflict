Ext.define('snap.view.orderdashboard.GopayzSalesDashboardController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.gopayzsalesdashboard-gopayzsalesdashboard',


    doSpotOrderSpecialSell: function(elemnt) {
        var me = this;

        var form = elemnt.lookupController().lookupReference('spotorderspecial-form').getForm();
        //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
        
        // Create forms
        spotorder = form.getFieldValues();
        //futureorder = form2.getFieldValues();
        productspotvalue = Ext.getCmp('gopayzproductspecial');
        
        // Total value to decimal 
        // Check Total Value
        if(spotorder.totalvalue != null){
            totalvalue = parseFloat(spotorder.totalvalue).toFixed(2);
        }else{
            totalvalue = 0;
        }
        // Check total xau weight
        if(spotorder.totalxauweight != null){
            totalxauweight = parseFloat(spotorder.totalxauweight).toFixed(3);
        }else{
            totalxauweight = 0;
        }

        // Check if product is not selected 
        if(spotorder.product == null){
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Product field is required',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }

        // Initialize product
        product = spotorder.product; 

        /*------------------------ Ace Buy Display ------------------------------------*/

        // Acquire Ace Buy 
        //acebuy = '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">' + 10 + '</span>';
        acebuy = spotorder.saleacebuyprice;
    
        // Slice Ace values
        acebuy = parseFloat(acebuy).toFixed(3);
        acebuy = acebuy.toString();
        acebuytruncatedleft = acebuy.slice(0, -5);
        //aceselltruncatedleft = parseFloat(aceselltruncatedleft);

        // Enlarged values of ace buy/sell (last 4)
        acebuytruncatedright = acebuy.substring(acebuy.length-5, acebuy.length);
        //aceselltruncatedright = parseFloat(aceselltruncatedright);
        
        /*------------------------ Ace Buy Price Confirmation------------------------------------*/
        // Set Color Codes
        // If value > previous
        if (spotorder.saleacebuypricechange = 'green'){
            // Green 
            backgroundcolor = '<div style="padding: 2.7em;background-color:#089000;">';
        }else if (spotorder.saleacebuypricechange = 'red'){
            // If value < previous
            // Red
            backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
        }else if (spotorder.saleacebuypricechange = 'grey'){
            // If no change
            backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
        }

        // Old Design
        /*
        acebuydesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
        '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
        'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' + 
        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';*/

        // New Design
        acebuydesignconfirmation = backgroundcolor + '<h1 style="color:#ffffff;display:inline;text-align:center;">' + 
        'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">' + acebuytruncatedright + '</p></h1>' + 
        '<h3 style="color:#ffffff;text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';
        
        // End of Ace Buy Price Confirmation
       // End of Ace Buy 
       /*--------------------------------------------- Check Backend if requirements are met ----------------------------------------*/

        // Get orderfees
        orderfees = vms.get('fees');
       
        fee = vms.get('product').selection.data;

        // Set Refinery Fee (temp)
        if (fee.refineryfee != null){
            refineryfeeraw = parseFloat(fee.refineryfee).toFixed(2);
        }else{
            refineryfeeraw = parseFloat(0).toFixed(2);
        }

        // Final Final Ace Buy Price
        acebuyprice = parseFloat(Ext.getCmp('gopayzsaleacebuyprice').getRawValue()).toFixed(3);
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
        if(totalxauweight == 0){
            finaltotalxauweight = parseFloat(totalvalue / finalacebuyprice).toFixed(3);
            totalestvalue = parseFloat(totalvalue).toFixed(2);
            finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
        }

        // Xau Weight inserted
        // Find Total Est Value
        // Ace buy price - refinery fee = final buy price

        // Final buy price  * xau weight = total est value
        if(totalestvalue == 0){
            totalestvalue = parseFloat(finalacebuyprice * totalxauweight).toFixed(2);
            finaltotalxauweight = totalxauweight;
        }
        

        // Acquire Refinery fee
        refineryfee = '<span style="color:#404040;font: 900 26px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">' + refineryfeeraw + '</span>';
        //debugger;
        //var aa= Ext.JSON.encode(params);
        //alert(aa);
        
        // Panel for Total Value
        var spotpanelbuytotalvalue = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                //{ xtype: 'displayfield', flex: 1},
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                    },]
                                },{   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            backgroundColor: 'white',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:false,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    
                                },
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Value (RM)</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        xtype: 'displayfield',  name:'Value', reference: 'value', value: totalvalue, flex: 1, fieldStyle: 'padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
                                    },]
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    xtype: 'panel',
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE BUY </span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame:false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 0 0 0',
                                    
                                },
                                { xtype: 'displayfield',
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;text-align:center;text-transform: uppercase;">Ace Buy</span>',
                                    //fieldLabel: 'ACE BUY',
                                    //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                    flex: 9,
                                    /*header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },*/
                                    id: 'salespotorderbuyconfirmationval',
                                    value: acebuydesignconfirmation,
                                    //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                    //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                    style: 'text-align:center;box-sizing:border-box;',
                                    name: 'acebuy',
                                    
                                    
                                    
                                },
                                {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    
                                    frame: false,
                                    style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
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
                                        xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Refining Fee', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },
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
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                //{ xtype: 'displayfield', flex: 1},
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                    },]
                                },{   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            backgroundColor: 'white',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:false,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    
                                },
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Total Xau Weight (gram)</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        xtype: 'displayfield',  name:'Value', reference: 'value', value: totalxauweight, flex: 1, fieldStyle: 'padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
                                    },]
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    xtype: 'panel',
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE BUY </span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame:false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 0 0 0',
                                    
                                },
                                { xtype: 'displayfield',
                                //fieldLabel: 'ACE BUY',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                id: 'salespotorderbuyconfirmationxau',
                                style: 'text-align:center;box-sizing:border-box;',
                                name: 'acebuy',
                                value: acebuydesignconfirmation,
                                /*
                                renderer: function(value, field) {

                                    if(spotorder = true){
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#A7EAAC;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }else if(spotorder = false){
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#F99B9B;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }else{
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#0A0A0A;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' +  acebuytruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }
                                   
                                        
                                    return this.rndTpl.apply({
                                        decimals: value
                                    });
                                },
                                listeners: {
                                    render: function(field, eOpts) {

                                        field.setValue(acebuytruncatedright);

                                    }
                                }*/},
                                {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    
                                    frame: false,
                                    style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
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
                                        xtype: 'displayfield', name:'refineryfee', reference: 'refineryfee', value: refineryfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Refining Fee', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },
                            ]
                        },    
                    ]
                },	
			],						
        });

        /*---------------------------------------- Unused Function ------------------------------------------------ */
        
        // Panel for Total Value
        var spotpanelselltotalvalue = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                { xtype: 'displayfield',
                //fieldLabel: 'ACE SELL',
                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                flex: 9,
                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                style: 'text-align:center;box-sizing:border-box;',
                id: 'salespotordersellconfirmationval',
                //value: aceselldesignconfirmation,
                name: 'acesell',
                
                },
                
            ]						
        });

        // Spot Panel for Total Xau Weight 
        var spotpanelselltotalxauweight = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                { xtype: 'displayfield',
                //fieldLabel: 'ACE SELL',
                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                flex: 9,
                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                style: 'text-align:center;box-sizing:border-box;',
                id: 'salespotordersellconfirmationxau',
                //value: aceselldesignconfirmation,
                name: 'acesell',
            },
                
            ]				
        });
        // End Unused Function
        
        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: 'Your request completed successfully.',
            layout: 'fit',
            width: 400,
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {   
                    title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1>',
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
                        xtype: 'displayfield', name:'product', reference: 'product', value: productspotvalue.rawValue, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Product', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'finalprice', reference: 'finalprice', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Final ACE Buy Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'xauweight', reference: 'xauweight', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'XAU Weight', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'totalestvalue', reference: 'totalestvalue', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Total est. value', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                    },
                    {
                        xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Order ID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                    },]
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
                    
                    customerid = forminput[5].getValue();
                    me._printOrderPDFSpotSpecial(btn, customerid);
                }
            }],
            listeners:{
                close:function(win) {
                    Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                    Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                    Ext.getCmp('salespotordersellconfirmationval').destroy();
                    Ext.getCmp('salespotordersellconfirmationxau').destroy();
                    
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
                    width: 600,
                    maxHeight: 700,
                    modal: true,
                    plain: true,
                    //closeAction: 'destroy',
                    buttonAlign: 'center',
                    buttons: [{
                        text: 'Submit',
                        handler: function(btn) {
                            if (spotpanelbuytotalvalue.getForm().isValid()) {
                                btn.disable();
                                snap.getApplication().sendRequest({
                                    hdl: 'order', action: 'doSpotOrderSpecial', 
                                        buyprice: Ext.getCmp('gopayzsaleacebuyprice').getRawValue(), 
                                        uuid: Ext.getCmp('gopayzsaleorderuuid').getRawValue(),
                                        amount: spotorder.totalvalue,
                                        productitem: spotorder.product,
                                        sellorbuy: 'buy',
                                        customerid: Ext.getCmp('gopayzcustomerspecial').getValue(),
                                        partnerid: spotorder.customer,
                                        reference: spotorder.referenceno,

                                }, 'Fetching data from server....').then(
                                //Received data from server already
                                function(data){
                                    if(data.success){
                                        
                                        // Clear input form
                                        Ext.getCmp('orderdashboardspotorderspecialgopayzform').reset();
                                         
                                        windowforordercomplete.items.items[0].items.items[1].setValue(data.return[0].price);
                                        windowforordercomplete.items.items[0].items.items[2].setValue(data.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                        // Set Total Est Value
                                        windowforordercomplete.items.items[0].items.items[3].setValue(data.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));

                                        windowforordercomplete.items.items[0].items.items[4].setValue(data.return[0].id);
                                        
                                        owningWindow = btn.up('window');
                                    
                                        //this.params.buyprice
                                        windowforordercomplete.show();
                                        // Set Final Buy Price
                                        owningWindow.close(); 

                                       

                                    }
                                });
                               
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: 'All fields are required',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                        } 
                    },{
                        text: 'Close',
                        handler: function(btn) {
                            owningWindow = btn.up('window');
                            owningWindow.close();
                           
                    
                        }
                    }],
                    listeners:{
                        close:function(win) {
                            Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                            Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                            Ext.getCmp('salespotordersellconfirmationval').destroy();
                            Ext.getCmp('salespotordersellconfirmationxau').destroy();

                        }
                    },
                    closeAction: 'destroy',
                    items: spotpanelbuytotalvalue
                });
                
        //var type=selectedRecords[0].get('type');            
        var windowforspotorderbuyxau = new Ext.Window({
            title: 'Confirmation..',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function(btn) {
                    if (spotpanelbuytotalxauweight.getForm().isValid()) {
                        btn.disable();
                        snap.getApplication().sendRequest({
                            hdl: 'order', action: 'doSpotOrderSpecial', 
                                buyprice: Ext.getCmp('gopayzsaleacebuyprice').getRawValue(), 
                                uuid: Ext.getCmp('gopayzsaleorderuuid').getRawValue(),
                                weight: spotorder.totalxauweight,
                                productitem: spotorder.product,
                                sellorbuy: 'buy',
                                customerid: Ext.getCmp('gopayzcustomerspecial').getValue(),
                                partnerid: spotorder.customer,
                                reference: spotorder.referenceno,
                        }, 'Fetching data from server....').then(
                        //Received data from server already
                        function(data){
                            if(data.success){

                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderspecialgopayzform').reset();

                                windowforordercomplete.items.items[0].items.items[1].setValue(data.return[0].price);
                                windowforordercomplete.items.items[0].items.items[2].setValue(data.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                // Set Total Est Value
                                windowforordercomplete.items.items[0].items.items[3].setValue(data.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));

                                windowforordercomplete.items.items[0].items.items[4].setValue(data.return[0].id);

                                owningWindow = btn.up('window');
                            
                                //this.params.buyprice
                                windowforordercomplete.show();
                                // Set Final Buy Price
                                owningWindow.close(); 
                                
                            }
                        });
                        
                    }else{
                        Ext.MessageBox.show({
                            title: 'Error Message',
                            msg: 'All fields are required',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });
                    }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    
                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                    
                }
            }],
            listeners:{
                close:function(win) {
                    Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                    Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                    Ext.getCmp('salespotordersellconfirmationval').destroy();
                    Ext.getCmp('salespotordersellconfirmationxau').destroy();

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
                allpermissions = vms.get('permissions');
                productpermission = allpermissions.find(x => x.id === spotorder.product);
        
                       
                // Run through form and check for empty fields
                forminput = form.getFields().items;
        
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
                for (index = 0; index <forminput.length; index++ ){
                    //alert(forminput[index].value);
                    // If field is not empty
                    if(forminput[index].value != ""){
                      
                        // Begin checking for empty fields
                        // If the filled fields are the 4 inputs
                        // Check id
                        if(forminput[index].id == 'gopayztotalvaluespotspecial' || forminput[index].id == 'gopayztotalxauweightspotspecial' || forminput[index].id == 'gopayzsaleacebuyprice'){
                            
                            // Begin checking for permission
                            // Checking if fields are filled
                            // Then save input combination for message display
                            // alert("Not EMPTY!" + forminput[index].id);
                            if(forminput[index].id == 'gopayztotalvaluespotspecial' && Ext.getCmp('gopayztotalvaluespotspecial').disabled == false){
                                
                                // Value is not null
                                checkvaluenull = 0;
                                // Check for permission
                                // Display warning if not true 
                                if(productpermission.bycurrency != true){
                                    // Set flag to indicate product has permission for value  
                                    checkvalue = 0;
                                    
                                }
                                //alert("Can value " + productpermission.bycurrency);
                            }
                            if(forminput[index].id == 'gopayztotalxauweightspotspecial' && Ext.getCmp('gopayztotalxauweightspotspecial').disabled == false){

                                // Checking if fields are filled
                               checkweightnull = 0;
                               // Check if weight is within reasonable range
                             
                               if(productpermission.weight != null){
                                   //debugger;
                                   // Ignore weights that are 0
                                   if(productpermission.weight != 0){
                                       // Check if modulus is 0
                                       // Enable weight checking
                                       doweightcheck = 1;
                                       //debugger;
                                       if((forminput[index].value % productpermission.weight) != 0){
                                           checkweightdivisible = 0;
                                       }else{
                                           checkweightdivisible = 1;
                                       }
                                       //debugger;
                                       //alert("this not 0, cant be divided");
                                   }
                               }
                               //forminput[index].value/
                               if(productpermission.byweight != true){
                                   // Set flag to indicate product has permission for value     
                                   checkweight = 0;
                                   
                               }
                               //alert("Can weight" + productpermission.byweight);
                           }//debugger;
                            if(forminput[index].id == 'gopayzsaleacebuyprice' && Ext.getCmp('gopayzsaleacebuyprice').disabled == false){
                                
                                // Checking if fields are filled
                                checkbuynull = 0;
       
                                if(productpermission.canbuy != true || productpermission.partnerCanSell != true){
                                   // Set flag to indicate product has permission for Buy
                                   checkbuy = 0;
                            
                               }
                               //alert("Can Buy " + productpermission.canbuy);
                           }
                            // End Checking for permission
                            
                        }else {
                            //Ext.getCmp(itemname).setHidden(true);
                        } // End Checking for empty fields
        
                    } // End Loop
                }
                

                // Do weight check
                if(doweightcheck == 1){
                    if (checkweightnull == 0 && checkbuynull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkweight == 0 && checkbuy == 1 && checkweightdivisible == 0){

                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });

                        }else if(checkweight == 0 && checkbuy == 1 && checkweightdivisible == 1){

                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });

                        }else if(checkweight == 0 && checkbuy == 0 && checkweightdivisible == 1){

                            // Weight = no
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else if(checkweight == 1 && checkbuy == 0 && checkweightdivisible == 0){

                            // Weight = yes
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else if(checkweight == 0 && checkbuy == 0 && checkweightdivisible == 0){
                            
                            // Weight = no
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else if(checkweight == 1 && checkbuy == 1 && checkweightdivisible == 0){

                            // Weight = yes
                            // Sell = no
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });

                        }else if(checkweight == 1 && checkbuy == 0 && checkweightdivisible == 1){

                            // Weight = yes
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else{
                            validformbuyweight = 1;
                        }
                        
                    }else {
                        
                        Ext.MessageBox.show({
                            title: 'Alert',
                            msg: 'Please fill mandatory fields',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                }else {
                    // Check permission and display accordingly
                    if (checkvaluenull == 0 && checkbuynull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkvalue == 0 && checkbuy == 1){

                            // Value = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Currency',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });

                        }else if(checkvalue == 1 && checkbuy == 0){

                            // Value = yes
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else if(checkvalue == 0 && checkbuy == 0){
                            
                            // Value = no
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Currency',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else{
                            validformbuytotal = 1;
                        }

                        
                    } else if (checkweightnull == 0 && checkbuynull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkweight == 0 && checkbuy == 1){

                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });

                        }else if(checkweight == 1 && checkbuy == 0){

                            // Weight = yes
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else if(checkweight == 0 && checkbuy == 0){
                            
                            // Weight = no
                            // Sell = no
                            if(productpermission.canbuy != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Buy This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanSell != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Sell This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }

                        }else{
                            validformbuyweight = 1;
                        }
                        
                    }else {
                        Ext.MessageBox.show({
                            title: 'Alert',
                            msg: 'Please fill mandatory fields',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                }
                
         /* ----------------------------------  End Check Permission for Spot Order Buy --------------------------------- */       

        if (totalvalue != null && totalxauweight == 0 && product != null && validformbuytotal == 1) {
 
            windowforspotorderbuytotal.show();
            
           
         } else if (totalvalue == 0 && totalxauweight != null && product != null && validformbuyweight == 1){
           

            windowforspotorderbuyxau.show();
            
           
         }else{
                Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                Ext.getCmp('salespotordersellconfirmationval').destroy();
                Ext.getCmp('salespotordersellconfirmationxau').destroy();
         }  
    },

    doSpotOrderSpecialBuy: function(elemnt) {
        var me = this;

        var form = elemnt.lookupController().lookupReference('spotorderspecial-form').getForm();
        //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
        
        // Create forms
        spotorder = form.getFieldValues();
        //futureorder = form2.getFieldValues();
        productspotvalue = Ext.getCmp('gopayzproductspecial');

        // Total value to decimal 
        // Check Total Value
        if(spotorder.totalvalue != null){
            totalvalue = parseFloat(spotorder.totalvalue).toFixed(2);
        }else{
            totalvalue = 0;
        }
        // Check total xau weight
        if(spotorder.totalxauweight != null){
            totalxauweight = parseFloat(spotorder.totalxauweight).toFixed(3);
        }else{
            totalxauweight = 0;
        }

        // Check if product is not selected 
        if(spotorder.product == null){
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Product field is required',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        
        // Init product
        product = spotorder.product;
       
        /*------------------------ Ace Buy Display ------------------------------------*/

        // Acquire Ace Buy 
        //acebuy = '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">' + 10 + '</span>';
        acesell = spotorder.saleacesellprice;

        // Slice Ace values
        acesell = parseFloat(acesell).toFixed(3);
        acesell = acesell.toString();
        aceselltruncatedleft = acesell.slice(0, -5);
        //aceselltruncatedleft = parseFloat(aceselltruncatedleft);
    
      
         // Enlarged values of ace buy/sell (last 4)
         aceselltruncatedright = acesell.substring(acesell.length-5, acesell.length);
        
        /*------------------------ Ace Sell Price Confirmation------------------------------------*/
        // Set Color Codesf
        // If value > previous
        if (spotorder.saleacesellpricechange = 'green'){
            // Green 
            backgroundcolor = '<div style="padding: 2.7em;background-color:#089000;">';
        }else if (spotorder.saleacesellpricechange = 'red'){
            // If value < previous
            // Red
            backgroundcolor = '<div style="padding: 2.7em;background-color:#c30101;">';
        }else if (spotorder.saleacesellpricechange = 'grey'){
            // If no change
            backgroundcolor = '<div style="padding: 2.7em;background-color:#777777;">';
        }

        // Old sell
        /*
        aceselldesignconfirmation = '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
        '<br>' + backgroundcolor + '<h1 style="color:#404040;display:inline;text-align:center;">' + 
        'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' + 
        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';
`       */
        aceselldesignconfirmation = backgroundcolor + '<h1 style="color:#ffffff;display:inline;text-align:center;">' + 
        'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">' + aceselltruncatedright + '</p></h1>' + 
        '<h3 style="color:#ffffff;text-align:center;font-style: italic;">per gram</h3>' +
        '</div>';
        
        // End of Ace Sell Price Confirmation
       // End of Ace Sell
       // Get orderfees
        orderfees = vms.get('fees');
        fee = vms.get('product').selection.data;
        
         // Set Premium Fee (temp)
         if (fee.premiumfee != null){
            premiumfeeraw = parseFloat(fee.premiumfee).toFixed(2);
        }else{
            premiumfeeraw = parseFloat(0).toFixed(2)
        }

        
        // Final Final Ace Sell Price
        acesellprice = parseFloat(Ext.getCmp('gopayzsaleacesellprice').getRawValue()).toFixed(3);
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
        if(totalxauweight == 0){
            finaltotalxauweight = parseFloat(totalvalue / finalacesellprice).toFixed(3);
            totalestvalue = parseFloat(totalvalue).toFixed(2);
            finaltotalxauweight = parseFloat(finaltotalxauweight).toFixed(3);
        }

        // Xau Weight inserted
        // Find Total Est Value
        // Ace buy price - refinery fee = final buy price

        // Final buy price  * xau weight = total est value
        if(totalestvalue == 0){
            totalestvalue = parseFloat(finalacesellprice * totalxauweight).toFixed(2);
            finaltotalxauweight = totalxauweight;
        }
        // Acquire Refinery fee
        premiumfee = '<span style="color:#404040;font: 900 26px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">' + premiumfeeraw + '</span>';
       
        //var aa= Ext.JSON.encode(params);
        //alert(aa);
        
        // Panel for Total Value
        var spotpanelselltotalvalue = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                //{ xtype: 'displayfield', flex: 1},
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                    },]
                                },{   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            backgroundColor: 'white',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:false,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    
                                },
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Value (RM)</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        xtype: 'displayfield',  name:'Value', reference: 'value', value: totalvalue, flex: 1, fieldStyle: 'padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
                                    },]
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    xtype: 'panel',
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE SELL </span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame:false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 0 0 0',
                                    
                                },
                                { xtype: 'displayfield',
                                //fieldLabel: 'ACE SELL',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                id: 'salespotordersellconfirmationval',
                                value: aceselldesignconfirmation,
                                name: 'acesell',
                                
                                },
                                {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    
                                    frame: false,
                                    style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
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
                                        xtype: 'displayfield', name:'premiunfee', reference: 'premiumfee', value: premiumfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Premium Fee', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },
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
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                //{ xtype: 'displayfield', flex: 1},
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Product</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        //xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                        xtype: 'displayfield', name:'Product', reference: 'productprice', value: productspotvalue.rawValue, fieldStyle: 'text-align:center;padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                                    },]
                                },{   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"> </span>',
                                    header: {
                                        style: {
                                            backgroundColor: 'white',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:false,
                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    
                                },
                                {   
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Total Xau Weight (gram)</span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    frame:true,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 10 0 0',
                                    items: [{
                                        xtype: 'displayfield',  name:'Value', reference: 'value', value: totalxauweight, flex: 1, fieldStyle: 'padding-left:12px;padding-top:15px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
                                    },]
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
                            flex: 6,
                        
                            //bodyPadding: 10,
                        
                            defaults: {
                                frame: false,
                            },
                        
                            items: [
                                {   
                                    xtype: 'panel',
                                    title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#FFFFFF;">ACE SELL </span>',
                                    header: {
                                        style: {
                                            backgroundColor: '#204A6D',
                                            display: 'inline-block',
                                            color: '#000000',
                                            
                                        }
                                    },
                                    //bodyStyle: 'background-color: yellow;',
                                    frame:false,
                                    //style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;letter-spacing: 1px;',
                                    //title: 'Ask',
                                    flex: 3,
                                    margin: '0 0 0 0',
                                    
                                },
                                { xtype: 'displayfield',
                                //fieldLabel: 'ACE SELL',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                id: 'salespotordersellconfirmationxau',
                                value: aceselldesignconfirmation,
                                name: 'acesell',/*
                                renderer: function(value, field) {

                                    if(spotorder = true){
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#A7EAAC;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }else if(spotorder = false){
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#F99B9B;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }else{
                                        this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                                        '<br><div style="padding: 0.7em;background-color:#0A0A0A;"><h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' +  aceselltruncatedleft + '<p style="font-size:130%;display:inline;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                    }
                                   
                                        
                                    return this.rndTpl.apply({
                                        decimals: value
                                    });
                                },
                                listeners: {
                                    render: function(field, eOpts) {

                                        field.setValue(aceselltruncatedright);

                                    }
                                }*/},
                                {   
                                    //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Refining Fee</span>',
                                    
                                    frame: false,
                                    style: 'opacity: 1.0;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
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
                                        xtype: 'displayfield', name:'premiunfee', reference: 'premiumfee', value: premiumfee , labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Premium Fee', flex: 1, style:'padding-left: 10px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                    },]
                                },
                                
                            ]
                        },    
                    ]
                },	
			],						
        });
        

        /*--------------------------------------- Unused Function ------------------------------*/
        
        // Panel for Total Value
        var spotpanelbuytotalvalue = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                { xtype: 'displayfield',
                //fieldLabel: 'ACE BUY',
                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                flex: 9,
                id: 'salespotorderbuyconfirmationval',
                //value: acebuydesignconfirmation,
                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                style: 'text-align:center;box-sizing:border-box;',
                name: 'acebuy',

                },
            ]					
        });

        // Spot Panel for Total Xau Weight 
        var spotpanelbuytotalxauweight = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                { xtype: 'displayfield',
                //fieldLabel: 'ACE BUY',
                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                flex: 9,
                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                id: 'salespotorderbuyconfirmationxau',
                style: 'text-align:center;box-sizing:border-box;',
                name: 'acebuy',
                //value: acebuydesignconfirmation,
               },
            
            ]						
        });
        
        // End Unused Function
        // Order Complete window
        var windowforordercomplete = new Ext.Window({
            title: 'Your request completed successfully.',
            layout: 'fit',
            width: 400,
            maxHeight: 700,
            modal: true,
            //closeAction: 'destroy',
            plain: true,
            buttonAlign: 'center',
            items: [
                {   
                    title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Order Complete</h1>',
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
                        xtype: 'displayfield', name:'product', reference: 'product', value: productspotvalue.rawValue, labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Product', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'finalprice', reference: 'finalprice', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Final ACE Sell Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'xauweight', reference: 'xauweight', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'XAU Weight', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield', name:'totalestvalue', reference: 'totalestvalue', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Total est. value', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: "color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;background-color: #ffffff ",
                    },
                    {
                        xtype: 'hiddenfield', name:'orderid', reference: 'orderid', value: '', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Order ID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:"color:#404040;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em; background-color: #ffffff ",
                    },]
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
                    
                    customerid = forminput[5].getValue();
                    me._printOrderPDFSpotSpecial(btn, customerid);
                    
                }
            }],
            listeners:{
                close:function(win) {
                    Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                    Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                    Ext.getCmp('salespotordersellconfirmationval').destroy();
                    Ext.getCmp('salespotordersellconfirmationxau').destroy();
                    
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
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function(btn) {
                    if (spotpanelselltotalvalue.getForm().isValid()) {
                        btn.disable();
                        snap.getApplication().sendRequest({ 
                            hdl: 'order', action: 'doSpotOrderSpecial', 
                                sellprice: Ext.getCmp('gopayzsaleacesellprice').getRawValue(), 
                                uuid: Ext.getCmp('gopayzsaleorderuuid').getRawValue(),
                                amount: spotorder.totalvalue,
                                productitem: spotorder.product,
                                sellorbuy: 'sell',
                                customerid: Ext.getCmp('gopayzcustomerspecial').getValue(),
                                partnerid: spotorder.customer,
                                reference: spotorder.referenceno,
                        }, 'Fetching data from server....').then(
                        //Received data from server already
                        function(data){
                            if(data.success){
                                                         
                                // Clear input form
                                Ext.getCmp('orderdashboardspotorderspecialgopayzform').reset();

                                windowforordercomplete.items.items[0].items.items[1].setValue(data.return[0].price);
                                windowforordercomplete.items.items[0].items.items[2].setValue(data.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                // Set Total Est Value
                                windowforordercomplete.items.items[0].items.items[3].setValue(data.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));

                                windowforordercomplete.items.items[0].items.items[4].setValue(data.return[0].id);

                                owningWindow = btn.up('window');
                                    
                                //this.params.buyprice
                                windowforordercomplete.show();
                                // Set Final Buy Price
                                owningWindow.close(); 
                                
                                
                                //Ext.getCmp('specialbalancesell').setValue(parseFloat(vms.get('product').selection.data.sellbalance -  windowforordercomplete.items.items[0].items.items[2].getValue()).toLocaleString('en', { minimumFractionDigits: 3 }));

                                //vms.get('product').selection.data.sellbalance  = Ext.getCmp('specialbalancesell').getValue();
                              
                              
                            }
                        });
                        
                    }else{
                        Ext.MessageBox.show({
                            title: 'Error Message',
                            msg: 'All fields are required',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });
                    }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    
                    owningWindow = btn.up('window');
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                    
                }
            }],
            listeners:{
                close:function(win) {
                    Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                    Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                    Ext.getCmp('salespotordersellconfirmationval').destroy();
                    Ext.getCmp('salespotordersellconfirmationxau').destroy();
                    
                }
            },
            closeAction: 'destroy',
            items: spotpanelselltotalvalue
        });

            //var type=selectedRecords[0].get('type');            
            var windowforspotordersellxau = new Ext.Window({
                title: 'Confirmation..',
                layout: 'fit',
                width: 600,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                buttons: [{
                    text: 'Submit',
                    handler: function(btn) {
                        if (spotpanelselltotalxauweight.getForm().isValid()) {
                            btn.disable();
                            snap.getApplication().sendRequest({
                                hdl: 'order', action: 'doSpotOrderSpecial', 
                                    sellprice: Ext.getCmp('gopayzsaleacesellprice').getRawValue(), 
                                    uuid: Ext.getCmp('gopayzsaleorderuuid').getRawValue(),
                                    weight: spotorder.totalxauweight,
                                    productitem: spotorder.product,
                                    sellorbuy: 'sell',
                                    customerid: Ext.getCmp('gopayzcustomerspecial').getValue(),
                                    partnerid: spotorder.customer,
                                    reference: spotorder.referenceno,
                            }, 'Fetching data from server....').then(
                            //Received data from server already
                            function(data){
                                if(data.success){
                                    
                                    // Clear input form
                                    Ext.getCmp('orderdashboardspotorderspecialgopayzform').reset();

                                    windowforordercomplete.items.items[0].items.items[1].setValue(data.return[0].price);
                                    windowforordercomplete.items.items[0].items.items[2].setValue(data.return[0].xau.toLocaleString('en', { minimumFractionDigits: 3 }));

                                    // Set Total Est Value
                                    windowforordercomplete.items.items[0].items.items[3].setValue(data.return[0].amount.toLocaleString('en', { minimumFractionDigits: 3 }));

                                    windowforordercomplete.items.items[0].items.items[4].setValue(data.return[0].id);

                                    owningWindow = btn.up('window');
                                        
                                    //this.params.buyprice
                                    windowforordercomplete.show();
                                    // Set Final Buy Price
                                    owningWindow.close(); 
                                    
                                    
                                    //Ext.getCmp('specialbalancesell').setValue(parseFloat(vms.get('product').selection.data.sellbalance -  windowforordercomplete.items.items[0].items.items[2].getValue()).toLocaleString('en', { minimumFractionDigits: 3 }));

                                    //vms.get('product').selection.data.sellbalance  = Ext.getCmp('specialbalancesell').getValue();
                                
                              
                                }
                            });
                            
                        }else{
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: 'All fields are required',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    } 
                },{
                    text: 'Close',
                    handler: function(btn) {
                        
                        owningWindow = btn.up('window');
                        //owningWindow.closeAction='destroy';
                        owningWindow.close();
                        
                    }
                }],
                listeners:{
                    close:function(win) {
                        Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                        Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                        Ext.getCmp('salespotordersellconfirmationval').destroy();
                        Ext.getCmp('salespotordersellconfirmationxau').destroy();
                        
                    }
                },
                closeAction: 'destroy',
                items: spotpanelselltotalxauweight
            });

        /* ---------------------------------- End Panel Graphics --------------------------------- */
        

         /* ---------------------------------- Check Permission for Spot Order Sell --------------------------------- */
                // Get Permission 
                allpermissions = vms.get('permissions');
                productpermission = allpermissions.find(x => x.id === spotorder.product);
        
                       
                // Run through form and check for empty fields
                forminput = form.getFields().items;
        
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
                for (index = 0; index <forminput.length; index++ ){
                    //alert(forminput[index].value);
                    // If field is not empty
                    if(forminput[index].value != ""){
                      
                        // Begin checking for empty fields
                        // If the filled fields are the 4 inputs
                        // Check id
                        if(forminput[index].id == 'gopayztotalvaluespotspecial' || forminput[index].id == 'gopayztotalxauweightspotspecial' || forminput[index].id == 'gopayzsaleacesellprice'){
                            
                            // Begin checking for permission
                            // Checking if fields are filled
                            // Then save input combination for message display
                            //alert("Not EMPTY!" + forminput[index].id);
                            if(forminput[index].id == 'gopayztotalvaluespotspecial' && Ext.getCmp('gopayztotalvaluespotspecial').disabled == false){
                                
                                // Value is not null
                                checkvaluenull = 0;
                                // Check for permission
                                // Display warning if not true 
                                if(productpermission.bycurrency != true){
                                    // Set flag to indicate product has permission for value  
                                    checkvalue = 0;
                                    
                                }
                                //alert("Can value " + productpermission.bycurrency);
                            }
                            if(forminput[index].id == 'gopayztotalxauweightspotspecial' && Ext.getCmp('gopayztotalxauweightspotspecial').disabled == false){

                                // Checking if fields are filled
                               checkweightnull = 0;
                               // Check if weight is within reasonable range
                             
                               if(productpermission.weight != null){
                                   //debugger;
                                   // Ignore weights that are 0
                                   if(productpermission.weight != 0){
                                       // Check if modulus is 0
                                       // Enable weight checking
                                       doweightcheck = 1;
                                       //debugger;
                                       if((forminput[index].value % productpermission.weight) != 0){
                                           checkweightdivisible = 0;
                                       }else{
                                           checkweightdivisible = 1;
                                       }
                                       //debugger;
                                       //alert("this not 0, cant be divided");
                                   }
                               }
                               //forminput[index].value/
                               if(productpermission.byweight != true){
                                   // Set flag to indicate product has permission for value     
                                   checkweight = 0;
                                   
                               }
                               //alert("Can weight" + productpermission.byweight);
                           }
                            if(forminput[index].id == 'gopayzsaleacesellprice' && Ext.getCmp('gopayzsaleacesellprice').disabled == false){
        
                                // Checking if fields are filled
                                checksellnull = 0;
       
                                if(productpermission.cansell != true || productpermission.partnerCanBuy != true){
                                   // Set flag to indicate product has permission for Sell
                                   checksell = 0;
                            
                               }
                               //alert("Can sell " + productpermission.cansell);
                           }
                            // End Checking for permission
                            
                        }else {
                            //Ext.getCmp(itemname).setHidden(true);
                        } // End Checking for empty fields
        
                    } // End Loop
                }
                
                if(doweightcheck == 1){
                    if (checkweightnull == 0 && checksellnull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkweight == 0 && checksell == 1 && checkweightdivisible == 0){
            
                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });
            
                        }else if(checkweight == 0 && checksell == 1 && checkweightdivisible == 1){
            
                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });
            
                        }else if(checkweight == 0 && checksell == 0 && checkweightdivisible == 1){
            
                            // Weight = no
                            // Sell = yes
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else if(checkweight == 1 && checksell == 0 && checkweightdivisible == 0){
            
                            // Weight = yes
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else if(checkweight == 0 && checksell == 0 && checkweightdivisible == 0){
                            
                            // Weight = no
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else if(checkweight == 1 && checksell == 1 && checkweightdivisible == 0){
                            
                            // Weight = no
                            // Sell = no
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Please Correct Your Weight Value, Weight Must Be Divided By ' + parseFloat(productpermission.weight).toFixed(3) + ' (g)',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });
            
                        }else if(checkweight == 1 && checksell == 0 && checkweightdivisible == 1){
                            
                            // Weight = no
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else{
                            validformsellweight = 1;
                        }
                        
                    }else {
                        Ext.MessageBox.show({
                            title: 'Alert',
                            msg: 'Please fill mandatory fields',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                }else {
                    // Check permission and display accordingly
                    if (checkvaluenull == 0 && checksellnull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkvalue == 0 && checksell == 1){
            
                            // Value = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, This Product Cannot Be Transacted By Currency',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });
            
                        }else if(checkvalue == 1 && checksell == 0){
            
                            // Value = yes
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else if(checkvalue == 0 && checksell == 0){
                            
                            // Value = no
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Currency',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else{
                            validformselltotal = 1;
                        }
            
                        
                    } else if (checkweightnull == 0 && checksellnull == 0){
                        // Check if the form fields have the corresponding permissions
                        if(checkweight == 0 && checksell == 1){
            
                            // Weight = no
                            // Sell = yes
                            Ext.MessageBox.show({
                                title: 'Alert',
                                msg: 'Sorry, ACE Cannot Sell This Product By Weight',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                            });
            
                        }else if(checkweight == 1 && checksell == 0){
            
                            // Weight = yes
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
            
                        }else if(checkweight == 0 && checksell == 0){
                            
                            // Weight = no
                            // Sell = no
                            if(productpermission.cansell != true ){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, Ace Do Not Sell This Product',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else if (productpermission.partnerCanBuy != true){
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, You Are Not Allowed To Buy This Product.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Alert',
                                    msg: 'Sorry, This Product Cannot Be Transacted By Weight',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                });
                            }
            
                        }else{
                            validformsellweight = 1;
                        }
                        
                    }else {
                        Ext.MessageBox.show({
                            title: 'Alert',
                            msg: 'Please fill mandatory fields',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                }
                
         /* ----------------------------------  End Check Permission for Spot Order Sell --------------------------------- */       

         ///////////////////////////////////////////////////////////////////    
         
        if (totalvalue != null && totalxauweight == 0 && product != null && validformselltotal == 1) {
            
            windowforspotorderselltotal.show();
            
           
         } else if (totalvalue == 0 && totalxauweight != null && product != null && validformsellweight == 1){
            
            windowforspotordersellxau.show();
            
           
         }else{
             /*
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please fill all required fields'});
            */
                Ext.getCmp('salespotorderbuyconfirmationval').destroy();
                Ext.getCmp('salespotorderbuyconfirmationxau').destroy();
                Ext.getCmp('salespotordersellconfirmationval').destroy();
                Ext.getCmp('salespotordersellconfirmationxau').destroy();
         }  
    },

    _printOrderPDFSpotSpecial: function(btn, customerid) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;
        
        // Get Printable data
        orderid = btn.up().up().items.items[0].items.items[4].getValue();

        var url = 'index.php?hdl=order&action=printSpotOrder&orderid='+orderid+'&customerid='+customerid;
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
						
						Ext.MessageBox.show({
							title: 'Error Message',
							msg: 'Failed to retrieve data',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});

    },

});
