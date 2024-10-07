Ext.define('snap.view.orderdashboard.GopayzSales',{
    extend: 'Ext.panel.Panel',
    xtype: 'gopayzsalesview',

    requires: [

        'Ext.layout.container.Fit',
        'snap.store.GopayzSalesPriceStream',
        'snap.view.orderdashboard.GopayzSalesDashboardController',

    ],
    viewModel: {
        data: {
            name: "Spot Order Special Gopayz",
            fees: [],
            permissions : [],
            dailylimit: [],
            products: [],
            customers: [],
            apicodescustomer: [],
            apicodesvendor: [],
        }
    },
    store: 'gopayzsalesPriceStream',	
    controller: 'gopayzsalesdashboard-gopayzsalesdashboard',
    formDialogWidth: 950,
    permissionRoot: '/root/go/vault',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyPadding: 25,
    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vms = this.getViewModel();
        Ext.create('snap.store.GopayzSalesPriceStream');

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
                        
                        Ext.getCmp('gopayzspotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#C0282E;border-radius:40px;padding: 0.5em;">Offline</span>')
                    }else if(data.status == 'online'){
                        Ext.getCmp('gopayzspotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#7ED321;border-radius:40px;padding: 0.5em;">Online</span>');
                    }else{
                        Ext.getCmp('gopayzspotorderspecialpricestatus').setValue('<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#C0282E;border-radius:40px;padding: 0.5em;">No Response</span>')
                    }
                    
                    Ext.getCmp('gopayzcustomerspecial').getStore().loadData(data.customers);
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
                      html: '<h1>Spot Order Special For GoPayz</h1>',
                      flex: 10,
                      margin: '20 10',
                      //xtype: 'orderview',
                     //reference: 'spotorder',
                    },{
                      // spacing in between
                      flex: 1,
                    },{
                      
                        layout: {
                            type: 'hbox',
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
                                    xtype: 'displayfield', id: 'gopayzspotorderspecialpricestatus', name:'pricestatus', reference: 'pricestatus', value: '<span style="color:#ffffff;font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;background-color:#ffffff;border-radius:40px;padding: 0.5em;"> - </span>', labelStyle: 'font: 900 16px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;display: inline-block', fieldLabel: 'Status', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },]
                            },
                        ]

                    }]
    
                },]
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
                    id: 'orderdashboardspotorderspecialgopayzform',
                    scrollable: false,
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
                                    xtype: 'hiddenfield', id: 'gopayzsaleacesellprice', name:'saleacesellprice', reference: 'saleacesellprice', fieldLabel: 'Ace Sell Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'gopayzsaleacebuyprice', name:'saleacebuyprice', reference: 'saleacebuyprice', fieldLabel: 'Ace Buy Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'gopayzsaleorderuuid', name:'uuid', reference: 'uuid', fieldLabel: 'UUID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'gopayzsaleacesellpricechange', name:'acesellpricechange', reference: 'acesellpricechange', fieldLabel: 'acesellpricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                {
                                    xtype: 'hiddenfield', id: 'gopayzsaleacebuypricechange', name:'acebuypricechange', reference: 'acebuypricechange', fieldLabel: 'acebuypricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                },
                                { xtype: 'combobox', flex: 1,fieldLabel:'Customer', id: 'gopayzcustomerspecial', style: 'margin-top:5%;', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'customer', valueField: 'id', displayField: 'name', reference: 'customer', forceSelection: false, editable: true,
                                labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                listeners: {
                                    select: function(combo, record, index) {
                                    
                                    // vms.get('dailylimit').find(x => x.id == combo.getValue())
                                 
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
                                   
                                      

                                      // Load products
                                      Ext.getCmp('gopayzproductspecial').getStore().loadData(customerproducts);
                                     
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
                                { xtype: 'combobox', flex:1, id: 'productspotspecial', fieldLabel:'Product', id: 'gopayzproductspecial', style: 'margin-top:5%;', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
                                labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
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
                                { xtype: 'textfield', flex: 1,fieldLabel: 'Reference No.', id: 'gopayzspotspecialreferenceno', name: 'referenceno', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', 
                                },
                                { xtype: 'textfield', flex: 1,fieldLabel: 'Total Value (RM)', id: 'gopayztotalvaluespotspecial', name: 'totalvalue', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', 
                                maskRe: /[0-9.-]/,
                                validator: function(v) {
                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                },
                                listeners: {
                                    change: function( fld, newValue, oldValue, opts ) {
                                        Ext.getCmp('gopayztotalxauweightspotspecial').disable();
                                        
                                        if(newValue == ''){
                                            Ext.getCmp('gopayztotalxauweightspotspecial').enable();
                                        }
                                    }                    
                                }},
                                {
                                    xtype : 'displayfield',
                                    width : '99%',
                                    padding: '0 5 0 5',
                                    value: "<h3 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h3>",
                                    //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                                    
                                },
                                { xtype: 'textfield', flex: 1, fieldLabel: 'Total Xau Weight (gram)', id: 'gopayztotalxauweightspotspecial', name: 'totalxauweight', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', 
                                maskRe: /[0-9.-]/,
                                validator: function(v) {
                                    return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                },
                                listeners: {
                                    change: function( fld, newValue, oldValue, opts ) {
                                        Ext.getCmp('gopayztotalvaluespotspecial').disable();
                                        
                                        if(newValue == ''){
                                            Ext.getCmp('gopayztotalvaluespotspecial').enable();
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
                                {   xtype: 'displayfield',
                                    //fieldLabel: 'ACE BUY',
                                    //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                    flex: 11,
                                    //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                    //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                    style: 'text-align:center;box-sizing:border-box;',
                                    name: 'acebuy',
                                    id: 'gopayzspotsaleacebuy',
                                    value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                                    '<br><div>' + 
                                    '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                    '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                    '</div>',

                                },         
                                { xtype: 'panel', flex: 1},
                                //{ xtype: 'displayfield', fieldLabel: 'Ask', name: 'acesell', dataIndex: 'priceChangePct', flex: 2 , renderer: 'renderPercent'},
                                {   xtype: 'displayfield',
                                    //fieldLabel: 'Ask',
                                    flex: 11,
                                    //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                    //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                    style: 'text-align:center;box-sizing:border-box;',
                                    name: 'acesell',
                                    id: 'gopayzspotsaleacesell',
                                    value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                                    '<br><div>' + 
                                    '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                    '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                    '</div>', 
                                
                                },
                                { xtype: 'displayfield', flex: 1},
                            ]
                        },
                    ],
                    dockedItems: [{
                        xtype: 'toolbar',
                        dock: 'bottom',
                        //ui: 'footer',
                        style: 'opacity: 1.0;',
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
                        items: [{
                                    xtype:'panel',
                                    flex:10
                                },{
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Sell</span>',
                                    handler: '',
                                    flex: 4,
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    tooltip: 'Sell Gold',
                                    reference: 'sell',                                   
                                    handler: 'doSpotOrderSpecialSell',
                                },{
                                    xtype:'panel',
                                    flex:4
                                  },{
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Buy</span>',
                                    handler: '',
                                    flex: 4,
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #d4af37 0%, #b08f26 100%);color: #ffffff;text-transform: uppercase;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    tooltip: 'Buy Gold',
                                    reference: 'buy',                                   
                                    handler: 'doSpotOrderSpecialBuy',
                                },{
                                    xtype:'panel',
                                    flex:2
                                },],
                    }],
                },]
            },
            {
                xtype: 'gridpanel',
                store: {
                    type: "GopayzOrder",
                },
                height: 240,
                columns: [
                    { text: 'Order ID', dataIndex: 'id', filter: { type: 'string' }, },
                    { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, },
                    { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130 },
                    { text: 'Order No', dataIndex: 'orderno', filter: { type: 'string' }, width: 120,
                        renderer: function (value, rec, rowrec) {
                            if (rowrec.data.type == 'CompanySell'){
                                rec.style = 'color:#209474'
                            }
                            if (rowrec.data.type == 'CompanyBuy'){
                                rec.style = 'color:#d07b32'
                            }
                            return Ext.util.Format.htmlEncode(value)
                        }, 
                    },
                    { text: 'Order Reference', dataIndex: 'partnerrefid', filter: { type: 'string' }, width: 150 },
                    { text: 'XAU', dataIndex: 'xau', xtype: 'numbercolumn', format: '0,000.000', align: 'right', filter: { type: 'string' }, width: 130 },
                    { text: 'Price', dataIndex: 'price', xtype: 'numbercolumn', format: '0,000.00', align: 'right', filter: { type: 'string' }, width: 130 },
                    { text: 'Total Amount', dataIndex: 'amount', xtype: 'numbercolumn', format: '0,000.00', align: 'right', filter: { type: 'string' }, width: 130 },
                    { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'string' }, width: 150 },
                ],
                bbar: {
                    xtype: 'pagingtoolbar',
                    displayInfo: true
                }
            },
            
        ]
    }


});
