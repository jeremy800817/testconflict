Ext.define('snap.view.orderdashboard.OrderDashboard',{
    extend: 'Ext.panel.Panel',
    xtype: 'orderdashboardview',

    requires: [

        'Ext.layout.container.Fit',
        'snap.view.orderdashboard.OrderDashboardController',
        'snap.view.orderdashboard.OrderDashboardModel',
        'snap.store.OrderPriceStream',


    ],
    viewModel: {
        data: {
            name: "Order",
            fees: [],
            permissions : [],
            status: '',

        }
    },
    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vm = this.getViewModel();

        Ext.create('snap.store.OrderPriceStream');
        async function getList(){
            const item_list = await snap.getApplication().sendRequest({
                hdl: 'orderdashboard', 'action': 'fillform',
                id: 1,
            }, 'Fetching data from server....').then(
            function(data) {
                if (data.success) {
                    //alert(data.fees);
                    vm.set('fees', data.fees);
                    // Set product permissions 
                    vm.set('permissions', data.permissions);

                    // Set Status
                    vm.set('status', data.status);

                    // If no products
                    /*
                    if (data.items){

                        Ext.getCmp('orderdashboardspotorderform').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                       
                    }else {
                        Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
                    }*/

                    // Check for PartnerId
                    if(data.haspartnerid){
                   
                        // Ext.getCmp('orderdashboardspotorderform').setHidden(false);
                        // Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                        Ext.getCmp('orderdashboardspotorderform').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                         
                     }else {
                         Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
                         Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
                     }

                    /* ****************************************** Old **********************************************************
                    if(data.usertype == 'Operator' || data.usertype == 'Sale'){
                   
                       // Ext.getCmp('orderdashboardspotorderform').setHidden(false);
                       // Ext.getCmp('orderdashboardfutureorderform').setHidden(false);

    
                        Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
                        // Reset Grid Data for List
                       
                        
                        
                    }else if (data.usertype == 'Customer' ){

                        Ext.getCmp('orderdashboardspotorderform').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                       
                    }else {
                        Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
                        Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
                    }
                    ****************************************** End Old ********************************************************** */

                    if(data.status == 'offline'){
                        
                        Ext.getCmp('spotorderonlinestatus').setText('Offline');
                        Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#C0282E');
                    }else if(data.status == 'online'){
                        Ext.getCmp('spotorderonlinestatus').setText('Online');
                        Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#4CAF50');
                    }else{
                        Ext.getCmp('spotorderonlinestatus').setText('No Response');
                        Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#C0282E');
                    }

                    // Set PartnerService permissions
                    //vm.set('fees', data.fees);

                    Ext.getCmp('productspot').getStore().loadData(data.items);
                    Ext.getCmp('productfuture').getStore().loadData(data.items);

                    //Ext.getCmp('userrefineryfee').getStore().loadData(data.items);

                    //Bid Price
                    //Ext.getCmp('bidpricedashboard').getStore().loadData(data.items);
                    //Ext.getCmp('askpricedashboard').getStore().loadData(data.items);
                    
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
    formDialogWidth: 950,
    permissionRoot: '/root/gtp/cust',
    //store: { type: 'Order' },
    store: 'orderPriceStream',	
    controller: 'orderdashboard-orderdashboard',
    formDialogWidth: 950,
    layout: 'fit',
    width: 500,
    height: 400,
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
    
        items: [
            // {
            //     // Style for migasit default
            //     style: {
            //         borderColor: '#204A6D',
            //     },
                
            //     height: 120,
            //     margin: '0 0 10 0',
            //     layout: 'fit',
            //     items: [{
            //         xtype: 'container',
            //         scrollable: false,
            //         layout: 'hbox',
            //         defaults: {
            //             //bodyPadding: '5',
            //             // border: true
            //         },
            //         items: [{
            //           html: '<h1>USD > MYR</h1>',
            //           flex: 10,
            //           margin: '20 10',
            //           //xtype: 'orderview',
            //          //reference: 'spotorder',
            //         },{
            //           // spacing in between
            //           flex: 1,
            //         },{
                      
            //             layout: {
            //                 type: 'hbox',
            //                 pack: 'start',
            //                 align: 'stretch'
            //             },
            //             flex: 6,
                    
            //             //bodyPadding: 10,
                    
            //             defaults: {
            //                 frame: false,
            //             },
                    
            //             items: [
            //                 {   
            //                     title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Bid</span>',
            //                     header: {
            //                         style: {
            //                             backgroundColor: 'white',
            //                             display: 'inline-block',
            //                             color: '#000000',
                                        
            //                         }
            //                     },
            //                     style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
            //                     //title: 'Ask',
            //                     flex: 3,
            //                     margin: '0 10 0 0',
            //                     items: [{
            //                         xtype: 'displayfield', id: 'bidpricedashboard', name:'bidprice', reference: 'bidprice', value: '-', fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
            //                     },]
            //                 },
            //                 {   
            //                     title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Ask</span>',
            //                     header: {
            //                         style: {
            //                             backgroundColor: 'white',
            //                             display: 'inline-block',
            //                             color: '#000000',
                                        
            //                         }
            //                     },
            //                     style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
            //                     //title: 'Ask',
            //                     flex: 3,
            //                     margin: '0 10 0 0',
            //                     items: [{
            //                         xtype: 'displayfield', id: 'askpricedashboard', name:'askprice', reference: 'askprice', value: '-', flex: 1, fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#C0282E;',
            //                     },]
            //                 },
            //             ]
            //         }]
    
            //     // id: 'medicalrecord',
            //     },]
            // },
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
                style: {
                    //backgroundColor: '#204A6D',
                    borderColor: '#red',
                },
                height: '80%',
                autoheight: true,
                items: [{
                    xtype: 'form',
                    title: 'Spot Order',
                    id: 'orderdashboardspotorderformblank',
                    padding : '0 5 0 0',
                    hidden: true,
                    border: true,
                    header: {
                        // Custom style for Migasit
                        /*style: {
                            backgroundColor: '#204A6D',
                        },*/
                        style : 'background-color: #204A6D;border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            text: '-',
                            reference: 'spotorder-status',
                           
                            //style: 'background-color: #B2C840'
                            style: 'background-color: #204A6D;border-radius: 20px;border-color: #204A6D',
                        }]
                    },
                    autoHeight: true,
                    flex: 13,
                    items: [ {
                        xtype : 'displayfield',
                        width : '99%',
                        padding: '0 1 0 1',
                        value: "<h5 style=' width:100%;text-align:center;line-height: normal; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>Your Account Has Not Been Mapped With a Partner, Please Contact GTP Admin</span></h5>",
                        //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                        
                    },]
                },
                {
                    xtype: 'form',
                    title: 'Spot Order',
                    reference: 'spotorder-form',
                    id: 'orderdashboardspotorderform',
                    hidden: true,
                    border: true,
                    header: {
                        // Custom style for Migasit
                        /*style: {
                            backgroundColor: '#204A6D',
                        },*/
                        style : 'border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            text: '-',
                            reference: 'spotorder-status',
                            id: 'spotorderonlinestatus',
                            //style: 'background-color: #B2C840'
                            style: 'border-radius: 20px;border-color: #204A6D',
                        }]
                    },
                    autoHeight: true,
                    flex: 13,
                    padding : '0 5 0 0',
                    align: 'stretch',
                    items: [
                        { xtype: 'combobox', allowblank: false, labelWidth : '39%', width : '99%', fieldLabel:'Product', flex: 1, id:'productspot', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            margin : '0 1 -10',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 0px; font-size: 16px;color:#757575;'></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                        { xtype: 'textfield', fieldLabel: 'Total Value (RM)', labelWidth : '39%', width : '99%', flex: 1, id:'totalvaluespotdashboard', name: 'totalvalue',  labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('totalxauweightspotdashboard').disable();
                                
                                if(newValue == ''){
                                    Ext.getCmp('totalxauweightspotdashboard').enable();
                                }
                            },                    
                        }},
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>OR</span></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                        
                        // Hidden records for price value
                        {
                            xtype: 'hiddenfield', id: 'acesellprice', name:'acesellprice', reference: 'acesellprice', fieldLabel: 'Ace Sell Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                            xtype: 'hiddenfield', id: 'acebuyprice', name:'acebuyprice', reference: 'acebuyprice', fieldLabel: 'Ace Buy Price', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                            xtype: 'hiddenfield', id: 'orderuuid', name:'uuid', reference: 'uuid', fieldLabel: 'UUID', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                            xtype: 'hiddenfield', id: 'acesellpricechange', name:'acesellpricechange', reference: 'acesellpricechange', fieldLabel: 'acesellpricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                            xtype: 'hiddenfield', id: 'acebuypricechange', name:'acebuypricechange', reference: 'acebuypricechange', fieldLabel: 'acebuypricechange', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },

                        { xtype: 'textfield', fieldLabel: 'Total XAU Weight (gram)', labelWidth : '39%', width : '99%', flex: 1, id:'totalxauweightspotdashboard', name: 'totalxauweight',  labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('totalvaluespotdashboard').disable();

                                if(newValue == ''){
                                    Ext.getCmp('totalvaluespotdashboard').enable();
                                }
                            }                    
                        },},
                        {
                            xtype: 'container',
                            scrollable: false,
                            layout: 'hbox',
                            defaults: {
                                bodyPadding: '5',
                                // border: true
                            },
                            signTpl: '<span style="' +
                                'color:{value:sign(\'"#cf4c35"\',\'"#73b51e"\')}"' +
                                '>{text}</span>',
                            flex: 1,
                            items:[
                                /*{ xtype: 'displayfield',
                                value: 191.111,
                                id: 'testrendertest',
                                //fieldLabel: 'ACE BUY',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                //value: 199.5119,
                                store: Ext.create('snap.store.OrderPriceStream'),
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                name: 'acebuy',
                                renderer: function(value, field) {
                                    this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Buy</h2>' +
                                        '<br><div>' + 
                                        '{[values.decimals.replace(/\\n/g, "<li/>")]}' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                                        
                                    return this.rndTpl.apply({
                                        decimals: value
                                    });
                                },
                                listeners: {
                                    render: function(field, eOpts) {
                                        var acebuy = field.rawValue;
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
                                        if (field.rawValue > 1){
                                            // Green 
                                            colortag = '<h1 style="color:#7ED321;display:inline;text-align:center;">RM ';
                                        }else if (field.rawvalue < 1){
                                            // If value < previous
                                            // Red
                                            colortag = '<h1 style="color:#C3262E;display:inline;text-align:center;">RM ';
                                        }else{
                                            // If no change
                                            colortag = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">RM ';
                                        }
                        
                                        //alert(acebuytruncatedleft);
                                        acebuyprice = colortag + acebuytruncatedleft + '<p style="font-size:130%;display:inline;">'+ acebuytruncatedright +'</p></h1>';
                                        

                                        field.setValue(acebuyprice);
                                    }
                                }},*/
                                { xtype: 'displayfield',
                                //value: 0.00,
                                //fieldLabel: 'ACE BUY',
                                //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                flex: 9,
                                id: 'spotacebuy',
                                //value: 199.5119,
                                //store: Ext.create('snap.store.OrderPriceStream'),
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                name: 'acebuy',
                                value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Buy (RM)</h2>' +
                                '<br><div>' + 
                                '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                '</div>',
                                },
                                { xtype: 'displayfield', flex: 1},
                                //{ xtype: 'displayfield', fieldLabel: 'Ask', name: 'acesell', dataIndex: 'priceChangePct', flex: 2 , renderer: 'renderPercent'},
                                { xtype: 'displayfield',
                                //fieldLabel: 'Ask',
                                flex: 9,
                                id: 'spotacesell',
                                //value: 0.00,
                                //style: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                //style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                style: 'text-align:center;box-sizing:border-box;',
                                name: 'acesell',
                                value: '<h2 style="text-align:center;text-transform: uppercase;">Ace Sell (RM)</h2>' +
                                '<br><div>' + 
                                '<p style="font-size:130%;display:inline;">No Price Data</p></h1>' + 
                                '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                '</div>', /*
                                renderer: function(value, field) {
                                    this.rndTpl = this.rndTpl || new Ext.XTemplate('<h2 style="text-align:center;text-transform: uppercase;">Ace Sell</h2>' +
                                        '<br><div>' + 
                                        '{[values.decimals.replace(/\\n/g, "<li/>")]}' + 
                                        '<h3 style="text-align:center;font-style: italic;">per gram</h3>' +
                                        '</div>');
                        
                                    return this.rndTpl.apply({
                                        decimals: value
                                    });
                                },
                                listeners: {
                                    render: function(field, eOpts) {
                                        var acesell = field.rawValue;
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
                                        if (field.rawValue < 1){
                                            // Green 
                                            colortag = '<h1 style="color:#7ED321;display:inline;text-align:center;">RM ';
                                        }else if (field.rawvalue > 1){
                                            // If value < previous
                                            // Red
                                            colortag = '<h1 style="color:#C3262E;display:inline;text-align:center;">RM ';
                                        }else{
                                            // If no change
                                            colortag = '<h1 style="color:#8BA2AF;display:inline;text-align:center;">RM ';
                                        }

                                        //alert(acebuytruncatedleft);
                                        acesellprice = colortag + aceselltruncatedleft + '<p style="font-size:130%;display:inline;">'+ aceselltruncatedright +'</p></h1>';
                                        

                                        field.setValue(acesellprice);
                                    }
                                }*/},
                            ]
                        },
                
                    ],
                    dockedItems: [{
                        xtype: 'toolbar',
                        dock: 'bottom',
                        style: 'opacity: 1.0;background: #ffffff;color: #ffffff; border-color: #ffffff; display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',      
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
                        items: [{
                                    xtype:'panel',
                                    flex:1
                                },{
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Sell</span>',
                                    handler: '',
                                    flex: 4,
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    tooltip: 'Sell Gold',
                                    reference: 'sell',                                   
                                    handler: 'doSpotOrderSell',
                                },/*
                                {
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Sell</span>',
                                    flex: 4,
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    tooltip: 'TEST',
                                    reference: 'TEST',
                                    handler: function(e) {
                          
                                        var x = 0;
                                        more = x + 1;

                                        Ext.getCmp('spotacebuy').setValue('<h1 style="color:#404040;display:inline;text-align:center;">' + 
                                        'RM' + 19 + '<p style="font-size:130%;display:inline;">'+ 5.117 + '</p></h1>');
                                      },
                                },*/{
                                    xtype:'panel',
                                    flex:2
                                  },{
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Buy</span>',
                                    handler: '',
                                    flex: 4,
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #d4af37 0%, #b08f26 100%);color: #ffffff;text-transform: uppercase;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    tooltip: 'Buy Gold',
                                    reference: 'editRequestToggler',
                                    handler: 'doSpotOrderBuy',

                                },{
                                    xtype:'panel',
                                    flex:1
                                },],
                    }]
                },
                {
                    xtype: 'form',
                    title: 'Future Order',
                    id: 'orderdashboardfutureorderformblank',
                    hidden: true,
                    flex: 13,
                    padding: '0 0 0 5',
                    
                    header: {
                        // Custom style for Migasit
                        /*style: {
                            backgroundColor: '#204A6D',
                        },*/
                        style : 'background-color: #204A6D;border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            //text: 'Offline',
                            //style: 'background-color: #C0282E;border-radius: 20px;border-color: #204A6D;'
                            style: 'background-color: #204A6D;border-radius: 20px;border-color: #204A6D;'
                        }]
                    },
                    border: true,
                    items: [ {
                        xtype : 'displayfield',
                        width : '99%',
                        padding: '0 1 0 1',
                        value: "<h5 style=' width:100%;text-align:center;line-height: normal; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>Your Account Has Not Been Mapped With a Partner, Please Contact GTP Admin</span></h5>",
                        //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                        
                    },]
                },
                {
                    xtype: 'form',
                    title: 'Future Order',
                    reference: 'futureorder-form',
                    id: 'orderdashboardfutureorderform',
                    hidden: true,
                    flex: 13,
                    padding: '0 0 0 5',
                    header: {
                        // Custom style for Migasit
                        /*style: {
                            backgroundColor: '#204A6D',
                        },*/
                        style : 'background-color: #204A6D;border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            //text: 'Offline',
                            //style: 'background-color: #C0282E;border-radius: 20px;border-color: #204A6D;'
                            style: 'background-color: #204A6D;border-radius: 20px;border-color: #204A6D;'
                        }]
                    },
                    border: true,
                    items: [
                        { xtype: 'combobox', fieldLabel:'Product', labelWidth : '39%', width : '99%', id:'productfuture', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            margin : '0 1 -10',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                        { xtype: 'textfield', fieldLabel: 'Total Value (RM)', labelWidth : '39%', width : '99%', id: 'totalvaluefuturedashboard', name: 'totalvalue',  labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('totalxauweightfuturedashboard').disable();
                                
                                if(newValue == ''){
                                    Ext.getCmp('totalxauweightfuturedashboard').enable();
                                }
                            }                    
                        }},
                        /*{
                            xtype: 'label',
                            html: "<div style='margin-top: 10px;'><b>OR</b></div>",
                        },*/
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>OR</span></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                        { xtype: 'textfield', fieldLabel: 'Total XAU Weight (gram)', labelWidth : '39%', width : '99%', id: 'totalxauweightfuturedashboard', name: 'totalxauweight',  labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('totalvaluefuturedashboard').disable();
                                
                                if(newValue == ''){
                                    Ext.getCmp('totalvaluefuturedashboard').enable();
                                }
                            }                    
                        }},
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                        { xtype: 'textfield', fieldLabel: 'ACE Buy Price (RM/g)',  labelWidth : '39%', width : '99%', id: 'acebuypricefuturedashboard', name: 'acebuyprice',  labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('acesellpricefuturedashboard').disable();
                                
                                if(newValue == ''){
                                    Ext.getCmp('acesellpricefuturedashboard').enable();
                                }
                            }                    
                        }},
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>OR</span></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                       /*{
                            xtype: 'label',
                            flex:1,
                            html: "<p style='float: left;font-weight: bold;width: 5%;display:inline-block'>OR</p><hr style='float: left;width: 80%;'>",
                        },*/
                        { xtype: 'textfield', fieldLabel: 'ACE Sell Price (RM/g)',  labelWidth : '39%',  width : '99%', id: 'acesellpricefuturedashboard', name: 'acesellprice', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        maskRe: /[0-9.-]/,
                        validator: function(v) {
                            return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                        },
                        listeners: {
                            change: function( fld, newValue, oldValue, opts ) {
                                Ext.getCmp('acebuypricefuturedashboard').disable();
                                
                                if(newValue == ''){
                                    Ext.getCmp('acebuypricefuturedashboard').enable();
                                }
                            }                    
                        }},
                    
                
                    ],

                    dockedItems: [{
                        xtype: 'toolbar',
                        dock: 'bottom',
                        //ui: 'dark',
                        style: 'opacity: 1.0;background: #ffffff;color: #ffffff; border-color: #ffffff; display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',      
                       
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
                                    flex:3.5
                                },{
                                    text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Queue Order</span>',
                                    handler: '',
                                    //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                    style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                    labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                    flex: 4,
                                    tooltip: 'Queue Order',
                                    reference: 'Queue Order',
                                    handler: 'doFutureOrderQueue'
                                    
                                },{
                                    xtype:'panel',
                                    flex:3.5
                                },],
                    }],
                }]

            // id: 'medicalrecord',
            },
        ]
    }


});
