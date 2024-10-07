Ext.define('snap.view.orderdashboard.Sales',{
    extend: 'Ext.panel.Panel',
    xtype: 'salesview',

    requires: [

        'Ext.layout.container.Fit',
        // 'snap.store.SalesPriceStream',
        'snap.view.orderdashboard.SalesDashboardController',

    ],
    viewModel: {
        data: {
            name: "Spot Order Special",
            fees: [],
            permissions : [],
            dailylimit: [],
            products: [],
            customers: [],
            apicodescustomer: [],
            apicodesvendor: [],
        }
    },
    // store: 'salesPriceStream',	
    controller: 'salesdashboard-salesdashboard',
    formDialogWidth: 950,
    permissionRoot: '/root/gtp/sale',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',
    // listeners: {
    //     afterrender: function () {
    //         elmnt = this;
    //         vms = this.getViewModel();
    //         this.store = "SalesPriceStream";
    //         Ext.create('snap.store.SalesPriceStream');

    //     }
    // }
    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vms = this.getViewModel();
        // Ext.create('snap.store.SalesPriceStream');
     
       
        async function getList(){
            const item_list = await snap.getApplication().sendRequest({
                hdl: 'orderdashboard', 'action': 'fillspecial',
                id: 1,
            }, 'Fetching data from server....').then(
            function(data) {
                if (data.success) {

                     //alert(data.fees);
                    vms.set('fees', data.fees);
                    // Set product permissions 
                    vms.set('permissions', data.permissions);
                    vms.set('dailylimit', data.customerdailylimit);
                    vms.set('products', data.items);
                    vms.set('customers', data.customers);

                    // Get this from database
                    vms.set('apicodescustomer', data.apicodescustomer);
                    vms.set('apicodesvendor', data.apicodesvendor);

                    // Set PartnerService permissions
                    //vm.set('fees', data.fees);
                    if(data.status == 'offline'){

                        Ext.getCmp('spotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#C0282E;border-radius:40px;padding: 0.5em;">Offline</span>')
                    }else if(data.status == 'online'){
                        Ext.getCmp('spotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">Online</span>');
                    }else{
                        Ext.getCmp('spotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#C0282E;border-radius:40px;padding: 0.5em;">No Response</span>')
                    }
                    
                    Ext.getCmp('customerspecial').getStore().loadData(data.customers);
                    //Ext.getCmp('productspecial').getStore().loadData(data.items);
                    //debugger;
                    //Ext.getCmp('salesorderhandlingpanel').collapse();
                    //alert(data.items);
                    //console.log('data_success')
                    //return data
                }
            });
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )
        this.callParent(arguments);
    },
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
        width: 500,
        height: 400,
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
            bodyPadding: 10
        },
    
        items: [
            {
                 // Style for migasit default
                 style: {
                    borderColor: '#204A6D',
                },
                
                height: 120,
                margin: '0 0 10 0',
                items: [{
                    xtype: 'container',
                    scrollable: false,
                    layout: 'hbox',
                    defaults: {
                        bodyPadding: '5',
                        // border: true
                    },
                    items: [{
                      html: '<h1>Spot Order Special</h1> (Please select a customer to get price)',
                      flex: 1,
                      margin: '20 10',
                      //xtype: 'orderview',
                     //reference: 'spotorder',
                    },{
                      
                       
                        flex: 1,
                        items: [
                            {   
                                //title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;"></span>',
                                header: {
                                    style: {
                                        backgroundColor: 'white',
                                        display: 'inline-block',
                                        color: '#000000',
                                        
                                    }
                                },
                                layout: {
                                    type: 'hbox',
                                    pack: 'end',
                                    align: 'middle'
                                },
                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                items: [{
                                    xtype: 'displayfield', id: 'spotorderspecialpricecode', name:'priceprovidercode', reference: 'priceprovidercode', value: '<span style="font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;padding: 0.5em;">-</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Price', style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },{
                                    xtype: 'displayfield', id: 'spotorderspecialpricestatus', name:'pricestatus', reference: 'pricestatus', value: '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#C0282E;border-radius:40px;padding: 0.5em;">No Response</span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Status', style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },]
                            },
                        ]

                    }]
    
                },]
            },
            {
                itemId: 'sales_fieldset',
                xtype: 'fieldset',
                title: 'Daily Limit',
                layout: 'hbox',
                defaultType: 'textfield',
                fieldDefaults: {
                    anchor: '100%',
                    msgTarget: 'side',
                    margin: '0 0 5 0',
                    width: '100%',
                },
                items: [
                        {
                          xtype: 'fieldcontainer',
                          //fieldLabel: 'Limits',
                          defaultType: 'textboxfield',
                          layout: 'hbox',
                          flex: 4,
                          items: [
                                    {
                                        xtype: 'fieldcontainer',
                                        layout: 'vbox',
                                        flex: 2,
                                        items: [
                                            {
                                                xtype: 'displayfield', id: 'speciallimitbuy', name:'limitbuy', reference: 'limitbuy', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Buy limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialpertransactionminbuy', name:'pertransactionminbuy', reference: 'pertransactionminbuy', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Per Transaction Min Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialpertransactionmaxbuy', name:'pertransactionmaxbuy', reference: 'pertransactionmaxbuy', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Per Transaction Max Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialbalancebuy', name:'balancebuy', reference: 'balancebuy', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Buy Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                            },
                                        ]
                                    },
                                  
                                ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            //fieldLabel: 'Balance',
                            defaultType: 'textboxfield',
                            layout: 'hbox',
                            flex: 4,
                            items: [
                                        {
                                            xtype: 'fieldcontainer',
                                            layout: 'vbox',
                                            flex: 2,
                                            items: [
                                            {
                                                xtype: 'displayfield', id: 'speciallimitsell', name:'limitsell', reference: 'limitsell', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Sell Limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialpertransactionminsell',name:'pertransactionminsell', reference: 'pertransactionminsell', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Per Transaction Min Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialpertransactionmaxsell',name:'pertransactionmaxsell', reference: 'pertransactionmaxsell', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Per Transaction Max Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                            },
                                            {
                                                xtype: 'displayfield', id: 'specialbalancesell', name:'balancesell', reference: 'balancesell', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', fieldLabel: 'Sell Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                            },
                                        ]
                                      },
                                    
                                  ]
                        },
                      ]
            },
            {
                /*
                //height: 120,
                title: '<h4 style="background:transparent; color:#404040; ">Spot Order Special</h4>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        
                    }
                },*/
                width: '50%',
                frame: false,
                margin: '0 0 10 0',
                items: [{
                    xtype: 'form',
                    //title: 'Spot Buy/Sell',
                    reference: 'spotorderspecial-form',
                    scrollable: false,
                    id: 'orderdashboardspotorderspecialform',
                    layout: 'hbox',
                    defaults: {
                        bodyPadding: '5',
                        // border: true
                    },
                    signTpl: '<span style="' +
                        'color:{value:sign(\'"#cf4c35"\',\'"#73b51e"\')}"' +
                        '>{text}</span>',
        
                    items:[
                        { xtype: 'container',
                            //fieldLabel: 'ACE BUY',
                            //style="border-style:dotted;border-color:1px solid #E3EFF4"
                            flex: 1,
                            autoheight: true,
                            items: [
                                {
                                    xtype: 'hiddenfield', id: 'saleacesellprice', name:'saleacesellprice', reference: 'saleacesellprice', fieldLabel: 'Ace Sell Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'saleacebuyprice', name:'saleacebuyprice', reference: 'saleacebuyprice', fieldLabel: 'Ace Buy Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'saleorderuuid', name:'uuid', reference: 'uuid', fieldLabel: 'UUID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'saleacesellpricechange', name:'acesellpricechange', reference: 'acesellpricechange', fieldLabel: 'acesellpricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'saleacebuypricechange', name:'acebuypricechange', reference: 'acebuypricechange', fieldLabel: 'acebuypricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                { xtype: 'combobox', flex: 1,fieldLabel:'Customer', id: 'customerspecial', style: 'margin-top:5%;', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'customer', valueField: 'id', displayField: 'name', reference: 'customer', forceSelection: false, editable: true,
                                labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                listeners: {
                                    select: function(combo, record, index) {
                                    
                                    // vms.get('dailylimit').find(x => x.id == combo.getValue())
                                    // On select
                                    Ext.getCmp('productspecial').setValue();

                                    Ext.getCmp('speciallimitbuy').setValue();
                
                                    Ext.getCmp('speciallimitsell').setValue();
                
                                    Ext.getCmp('specialbalancebuy').setValue();
                                    Ext.getCmp('specialbalancesell').setValue();
                
                                    Ext.getCmp('specialpertransactionminbuy').setValue();
                                    Ext.getCmp('specialpertransactionminsell').setValue();
                
                                    Ext.getCmp('specialpertransactionmaxbuy').setValue();
                                    Ext.getCmp('specialpertransactionmaxsell').setValue();

                                    // Check for appropriate product for selected customer and load it
                                    productlist = vms.get('products');

                                    // Get Customer record based on box selection 
                                    customerinfo = vms.get('customers').find(x => x.id == combo.getValue());

                                    // Get Customer Products
                        
                                    // Old customer product
                                    //customerproducts = productlist.filter(x => x.partnerid === customerinfo.partnerid);

                                    // Mew customer product
                                    if(productlist){
                                        customerproducts = productlist.filter(x => x.partnerid === customerinfo.id);
                                        // Check if partner have product service 
                                        if (customerproducts.length == 0) {
                                            Ext.MessageBox.show({
                                                title: "ERROR-TS01",
                                                msg: "Selected customer does not have any linked products",
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.WARNING
                                            });     
                                        }
                                    }else {
                                        Ext.MessageBox.show({
                                            title: "ERROR-TS01",
                                            msg: "Selected customer does not have any linked products",
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.WARNING
                                        });     
                                    }
                                    
                                    // Build new url socket
                                    // Check if store exist, if yes destroy before loading
                                    if(this.priceStreamStore){
                                        // this.priceStreamStore.getProxy().setUrl(newurl);
                                        this.priceStreamStore.destroy();
                                        Ext.getCmp('spotorderspecialpricecode').setValue('<span style="font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;padding: 0.5em;">-</span>');
                                        // Clear price
                                        defaulttextvalue = '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy (RM)</h2>' +
                                        '<br><div>' + 
                                        '<p style="font-size:130%;display:inline;">Loading New Price</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>';
                                        Ext.getCmp('spotsaleacebuy').setValue(defaulttextvalue);
                                        Ext.getCmp('spotsaleacesell').setValue(defaulttextvalue);
                                    }
                                  

                                    newurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; newurl += window.location.hostname + '/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&code='+customerinfo.priceprovidercode;
                                    // newurl = 'wss://gtp2uat.ace2u.com/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&air=1&code='+customerinfo.priceprovidercode;
                                    this.priceStreamStore = Ext.create('snap.store.SalesPriceStream', {
                                        // storeId: 'banaka',
                                        proxy: {
                                            type: 'websocket',
                                            storeId: 'salesPriceStream',
                                            //url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9',	
                                            url: newurl,	
                                            //url: 'ws://bo.gtp.development:80/nchan/sub',
                                            //url: 'ws://bo.gtp.development/nchan/sub/1',
                                            reader: {
                                                type: 'json',
                                                rootProperty: 'data',
                                                transform: {
                                                    fn: function (data) {
                                                        
                                                        
                                                        // Set Code
                                                        Ext.getCmp('spotorderspecialpricecode').setValue('<span style="font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;padding: 0.5em;">'+customerinfo.priceprovidercode+'</span>');

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
                                                   
                                                        Ext.getCmp('saleacesellprice').setValue(companysellppg);
                                                        Ext.getCmp('saleacebuyprice').setValue(companybuyppg);
                                                        Ext.getCmp('saleorderuuid').setValue(data[0].uuid);
                                                        
                                                        prevbuyprice = companybuyppg;
                                                        prevsellprice = companysellppg;
                                                        
                                                        Ext.getCmp('spotsaleacebuy').setValue(acebuydesign);
                                                        Ext.getCmp('spotsaleacesell').setValue(aceselldesign);
                                                        
                                                        
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
                                    });
                                    // priceStreamStore.proxy.extraParams ={  param1: 'value1', param2: 'value2' };
                                    // priceStreamStore.getProxy().setExtraParams({"idOdontologia" :'aaa'});
                                     this.priceStreamStore.load();
                                 

                                      // Load products
                                      Ext.getCmp('productspecial').getStore().loadData(customerproducts);
                                     
                                    }
                                },
                                tpl: [
                                    '<ul class="x-list-plain">',
                                    '<tpl for=".">',
                                    '<li class="',
                                    Ext.baseCSSPrefix, 'grid-group-hd ',
                                    Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                                    '<li class="x-boundlist-item">',
                                    '{name}',
                                    '</li>',
                                    '</tpl>',
                                    '</ul>'
                                ]},
                                { xtype: 'combobox', flex:1, id: 'productspotspecial', fieldLabel:'Product', id: 'productspecial', style: 'margin-top:5%;', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
                                labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                listeners: {
                                    select: function(combo, record, index) {

                                      // vms.get('dailylimit').find(x => x.id == combo.getValue())
                                      // On select
                                      

                                      //**************************************************** Get daily limit ****************************************************************/

                                      //customerpartnerid = vms.get('customer').selection.data.partnerid;

                                      // Get proper products based on customer's partner
                                      //customerproductlist = vms.get('products').filter(x => x.partnerid === vms.get('customer').selection.data.partnerid);

                                      //debugger;
                                      Ext.getCmp('speciallimitbuy').setValue(parseFloat(vms.get('product').selection.data.dailybuylimitxau).toLocaleString('en', { minimumFractionDigits: 3 }));
                                      
                                      Ext.getCmp('speciallimitsell').setValue(parseFloat(vms.get('product').selection.data.dailyselllimitxau).toLocaleString('en', { minimumFractionDigits: 3 }));
                    
                                      Ext.getCmp('specialbalancebuy').setValue(parseFloat(vms.get('product').selection.data.buybalance).toLocaleString('en', { minimumFractionDigits: 3 }));
                                      Ext.getCmp('specialbalancesell').setValue(parseFloat(vms.get('product').selection.data.sellbalance).toLocaleString('en', { minimumFractionDigits: 3 }));
                    
                                      Ext.getCmp('specialpertransactionminbuy').setValue(parseFloat(vms.get('product').selection.data.buyclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }));
                                      Ext.getCmp('specialpertransactionminsell').setValue(parseFloat(vms.get('product').selection.data.sellclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }));
                    
                                      Ext.getCmp('specialpertransactionmaxbuy').setValue(parseFloat(vms.get('product').selection.data.buyclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }));
                                      Ext.getCmp('specialpertransactionmaxsell').setValue(parseFloat(vms.get('product').selection.data.sellclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }));

                                      // Check for appropriate product for selected customer and load it
                                      //productlist = vms.get('products');

                                      /*
                                      // Get Customer record based on box selection 
                                      customerinfo = vms.get('customers').find(x => x.id == combo.getValue());

                                      // Get Customer Products
                                      
                                      customerproducts = productlist.filter(x => x.partnerid === customerinfo.partnerid);
                                      // Load products
                                      Ext.getCmp('productspecial').getStore().loadData(customerproducts);*/
                                     
                                    }
                                },
                                tpl: [
                                    '<ul class="x-list-plain">',
                                    '<tpl for=".">',
                                    '<li class="',
                                    Ext.baseCSSPrefix, 'grid-group-hd ',
                                    Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                                    '<li class="x-boundlist-item">',
                                    '{name}',
                                    '</li>',
                                    '</tpl>',
                                    '</ul>'
                                ]},
                                { xtype: 'textfield', flex: 1,fieldLabel: 'Total Value (RM)', id: 'totalvaluespotspecial', name: 'totalvalue', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', 
                                maskRe: /[0-9.-]/,
                                validator: function(v) {
                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                },
                                listeners: {
                                    change: function( fld, newValue, oldValue, opts ) {
                                        Ext.getCmp('totalxauweightspotspecial').disable();
                                        
                                        if(newValue == ''){
                                            Ext.getCmp('totalxauweightspotspecial').enable();
                                        }
                                    }                    
                                }},
                                {
                                    xtype : 'displayfield',
                                    width : '99%',
                                    padding: '0 5 0 5',
                                    value: "<h3 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>OR</span></h3>",
                                    //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                                    
                                },
                                { xtype: 'textfield', flex: 1, fieldLabel: 'Total XAU Weight (gram)', id: 'totalxauweightspotspecial', name: 'totalxauweight', labelWidth : '39%', width : '99%', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', 
                                maskRe: /[0-9.-]/,
                                validator: function(v) {
                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                },
                                listeners: {
                                    change: function( fld, newValue, oldValue, opts ) {
                                        Ext.getCmp('totalvaluespotspecial').disable();
                                        
                                        if(newValue == ''){
                                            Ext.getCmp('totalvaluespotspecial').enable();
                                        }
                                    }                    
                                }},
                            ]
                        },
                        //{ xtype: 'displayfield', flex: 1},
                        //{ xtype: 'displayfield', fieldLabel: 'Ask', name: 'acesell', dataIndex: 'priceChangePct', flex: 2 , renderer: 'renderPercent'},
                        {
                            xtype: 'container',
                            style: 'padding:2em 1em;box-sizing:border-box;',
                            scrollable: false,
                            layout: 'hbox',
                            defaults: {
                                bodyPadding: '5',
                                // border: true
                            },
                            flex: 2,
                            signTpl: '<span style="' +
                                'color:{value:sign(\'"#cf4c35"\',\'"#73b51e"\')}"' +
                                '>{text}</span>',
                
                            items:[
                                {
                                    xtype: 'panel',
                                    flex: 2,
                                    style: 'text-align: center;',
                                    items: [
                                        {   xtype: 'displayfield',
                                            width:'100%',

                                            //fieldLabel: 'ACE BUY',
                                            //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                            flex: 1,
                                            //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                            //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                            style: 'text-align:center;box-sizing:border-box;',
                                            name: 'acebuy',
                                            id: 'spotsaleacebuy',
                                            value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy (RM)</h2>' +
                                            '<br><div>' + 
                                            '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                            '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                            '</div>',

                                        },
                                        {
                                            xtype: 'button',
                                            padding: '8px 30px',
                                            width: '130px',
                                            // paddingRight: '120px',
                                            text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Sell</span>',
                                            handler: '',
                                            //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                            style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                            tooltip: 'Sell Gold',
                                            reference: 'sell',                                   
                                            handler: 'doSpotOrderSpecialSell',
                                        },
                                    ]
                                },
                                {
                                    xtype: 'panel',
                                    flex: 2,
                                    style: 'text-align: center;',
                                    items: [
                                    {   
                                        xtype: 'displayfield',
                                        width:'100%',
                                        
                                        //fieldLabel: 'Ask',
                                        flex: 1,
                                        //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                        //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                        style: 'text-align:center;box-sizing:border-box;',
                                        name: 'acesell',
                                        id: 'spotsaleacesell',
                                        value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell (RM)</h2>' +
                                        '<br><div>' + 
                                        '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>', 
                                    
                                    },{
                                        xtype: 'button',
                                        padding: '8px 30px',
                                        width: '130px',
                                        text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Buy</span>',
                                        handler: '',
                                        //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #d4af37 0%, #b08f26 100%);color: #ffffff;text-transform: uppercase;',
                                        style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        tooltip: 'Buy Gold',
                                        reference: 'buy',                                   
                                        handler: 'doSpotOrderSpecialBuy',
                                    },]
                                }
                            ]
                        },
                    ],
                    
                },]
            },
            
            
        ]
    }


});
