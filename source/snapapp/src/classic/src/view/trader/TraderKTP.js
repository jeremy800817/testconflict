Ext.define('snap.view.trader.TraderKTP',{
    extend: 'Ext.panel.Panel',
    // extend: 'snap.view.gridpanel.Base',
    xtype: 'traderktpview',
    
    requires: [

        // 'Ext.layout.container.Fit',
        // // 'snap.view.orderdashboard.OrderDashboardController',
        // 'snap.view.trader.TraderModel',
        // 'snap.store.TraderPrice',
        // 'Ext.util.format.number'

    ],
    viewModel: {
        data: {
            name: "Order",
            fees: [],
            permissions : [],
            status: '',
            intlX: [],
            intlXAU: [],
        },
        
    },

    initComponent: function(){
        
        _this = this;
        vm = this.getViewModel();
        var websocket = Ext.create ('Ext.ux.WebSocket', {
            // initComponent: function(){
            //     Ext.on('unmatchedroute', function (hash) {
            //         console.log("trader close2")
            //     })
            // },
            // url: 'wss://shouty10.ace2u.com/socket.io/?EIO=3&transport=websocket' ,
            url: 'wss://snappy.ace2u.com/socket.io/?EIO=3&transport=websocket' ,
            listeners: {
                open: function (ws) {
                    // console.log (websocket, this, ws,'The websocket is ready to use');
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    
                    var tempm = parseInt(message);
                    var charx = 0;
                    if (tempm.toString() == '0' || tempm.toString() == 'NaN'){
                        return;
                    }
                    charx = tempm.toString().length
                    var msg = message.substring(charx);
                    if (msg === ''){
                        return;
                    }
                    x = Ext.JSON.decode(msg);
                    
                    vm.set(x[0], x[1])
                    connection = ['intlX', 'connect', 'connect_error', 'reconnect_failed', 'reconnecting']
                    if (connection.includes(x[0])){
                        _this.pormt_connection(x[0]);
                    }
                   
                    if ('tradeopen' == x[0]){
                        _this.promt_tradeopen(x[1]);
                    }
                    if ('goodfeed' == x[0]){
                        _this.promt_goodfeed(x[1]);
                    }

                    fx = ["xaumyrx", "eurfxu", "audfxu", "nzdfxu", "gbpfxu"];
                    if (fx.includes(x[0])){
                        _this.pormt_fxchange(x[0], x[1]);
                    }
                    
                }
            },
        });
        // onUnmatchedRoute: function(){
        // }

        // var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1';
        // var websocket2 = Ext.create ('Ext.ux.WebSocket', {
        //     url: websocketurl,
        //     // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
        //     listeners: {
        //         open: function (ws) {
        //         } ,
        //         close: function (ws) {
        //             console.log ('The websocket is closed!');
        //         },
        //         error: function (ws, error) {
        //             Ext.Error.raise ('ERRRROR: ' + error);
        //         } ,
        //         message: function (ws, message) {
        //             message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
        //             message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
        //             message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
        //             vm.set("bumirapublicchannel", message.data[0]);

        //         }
        //     },
        // });


        var websocketurl_dealer = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&dealer1=1';
        var websocket3 = Ext.create ('Ext.ux.WebSocket', {
            url: (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&dealer1=1',
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    spread = parseFloat(message.data[0].companysell) - parseFloat(message.data[0].companybuy);
                    message.data[0].spread = spread.toFixed(3);
                    vm.set("dealerchannel", message.data[0]);
                }
            },
        });
        var websocketurl_mks = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mks=1';
        var websocket4 = Ext.create ('Ext.ux.WebSocket', {
            url: (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mks=1',
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("mkschannel", message.data[0]);

                }
            },
        });
        var websocketurl_pkb = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.Pkb';
        var websocket6 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_pkb,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("pkbchannel", message.data[0]);

                }
            },
        });
        // var websocketurl_pkb = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.pkb';
        // var websocket5 = Ext.create ('Ext.ux.WebSocket', {
        //     url: websocketurl_pkb,
        //     // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
        //   // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
        //     listeners: {
        //         open: function (ws) {
        //         } ,
        //         close: function (ws) {
        //             console.log ('The websocket is closed!');
        //         },
        //         error: function (ws, error) {
        //             Ext.Error.raise ('ERRRROR: ' + error);
        //         } ,
        //         message: function (ws, message) {
        //             message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
        //             message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
        //             message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
        //             vm.set("pkbchannel", message.data[0]);

        //         }
        //     },
        // });
        var websocketurl_pkbloan = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.PkbLoan';
        var websocket6 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_pkbloan,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("pkbloanchannel", message.data[0]);

                }
            },
        });
        var websocketurl_pkbaffiliate = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.PkbAffiliate';
        var websocket7 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_pkbaffiliate,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("pkbaffiliatechannel", message.data[0]);

                }
            },
        });

        var websocketurl_pkbpublic = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.PkbPublic';
        var websocket7 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_pkbpublic,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("pkbpublic", message.data[0]);

                }
            },
        });
        var websocketurl_bumira = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.Bumira';
        var websocket7 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_bumira,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("bumirachannel", message.data[0]);

                }
            },
        });
        var websocketurl_bumiraloan = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.BumiraLoan';
        var websocket7 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_bumiraloan,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("bumiraloanchannel", message.data[0]);

                }
            },
        });
        var websocketurl_bumirapublic = (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code=INTLX.BumiraPublic';
        var websocket7 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl_bumirapublic,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
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
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("bumirapublicchannel", message.data[0]);

                }
            },
        });
        this.callParent(arguments);
    },
    
    listeners: {
        onUnmatchedRoute: function(){
            console.log("trader close1")
        }
    },

    pormt_fxchange: function(key_name, key_val){
        vm = this.getViewModel();
        fxr = vm.get("fxr");
        var prefix = 'c_';
        var _return_key = prefix.concat(key_name);
        var _return = []
        if (!fxr){
            return;
        }
        if (key_name == 'xaumyrx'){
            _return.buy = (key_val.buy / fxr.bid).toFixed(4)
            _return.sell = (key_val.sell / fxr.ask).toFixed(4)
        }else{
            _return.buy = (fxr.bid * key_val.buy).toFixed(4)
            _return.sell = (key_val.sell * fxr.ask).toFixed(4)
        }
        
        vm.set(_return_key, _return)
    },
    pormt_connection: function(data, num = null){
        vm = this.getViewModel();
        if (data == 'connect' || data == 'intlX'){
            html = '<p style="color:aqua">LIVE</p>'
        }
        if (data == 'connect_error'){
            html = '<p style="color:brown">ERROR</p>'
        }
        if (data == 'reconnect_failed'){
            html = '<p style="color:red">OFFLINE</p>'
        }
        if (data == 'reconnecting'){
            html = '<p style="color:red">OFFLINE</p>'
        }
        vm.set('connection', {text: html})
    },
    promt_goodfeed: function(data){
        vm = this.getViewModel();
        if (data == 'true'){
            html = '<p style="color:green">GOOD</p>'
        }else{
            html = '<p style="color:blue">BAD</p>'
        }
        vm.set('goodfeed', {text: html})
    },
    promt_tradeopen: function(data){
        vm = this.getViewModel();
        if (data == 'true'){
            html = '<p style="color:green">OPEN</p>'
        }else{
            html = '<p style="color:red">CLOSE</p>'
        }
        vm.set('tradeopen', {text: html})
    },

   
    // formDialogWidth: 950,
    // permissionRoot: '/root/trading/ktporder',
    formDialogWidth: 950,
    permissionRoot: '/root/system/trader/ktplist',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',


    items: {
        xtype: 'container',
        layout: "fit",
        cls: 'trader-container',
        height: 1000,
            items:[
                

                {
                defaults: {
                    width: 400,
                    height: 250,
                    // bodyPadding: 10,
                    autoShow: true,
                    cls: ['trader-container'],
                },
            
                profiles: {
                    classic: {
                        width: 200
                    },
                    neptune: {
                        width: 200
                    },
                    graphite: {
                        width: 260
                    },
                    'classic-material': {
                        width: 260
                    }
                },
                
                items: [
                    {
                        xtype: 'window',
                        title: 'PKB Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 0, y: 0, alwaysOnTop: 8,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbchannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbchannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbchannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbchannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbchannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbchannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                    {
                        xtype: 'window',
                        title: 'PKB Loan Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 300, y: 0, alwaysOnTop: 8,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbloanchannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbloanchannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbloanchannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbloanchannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbloanchannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbloanchannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                    {
                        xtype: 'window',
                        title: 'PKB Affiliate Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 600, y: 0, alwaysOnTop: 8,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbaffiliatechannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbaffiliatechannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbaffiliatechannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbaffiliatechannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbaffiliatechannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbaffiliatechannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                    {
                        xtype: 'window',
                        title: 'PKB Public Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 900, y: 0, alwaysOnTop: 8,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbpublic.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbpublic.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbpublic.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{pkbpublic.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbpublic.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{pkbpublic.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                    // Border Split with Bumira
                    {
                        xtype: 'window',
                        title: 'Bumira Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 0, y: 395, alwaysOnTop: 4,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirachannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirachannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirachannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirachannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumirachannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumirachannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                  {
                        xtype: 'window',
                        title: 'Bumira Loan Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 300, y: 395, alwaysOnTop: 5,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumiraloanchannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumiraloanchannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumiraloanchannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumiraloanchannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumiraloanchannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumiraloanchannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    },
                    {
                        xtype: 'window',
                        title: 'Bumira Public Open GP',
                        headerPosition: 'top',
                        constrain: true,
                        x: 600, y: 395, alwaysOnTop: 7,
                        width: 300,
                        height: 280,
                        cls: ['background-blue','tradeprice-window'],
                        items: [{
                            xtype: 'container',
                            padding: '10px',
                            layout: {
                                type:'table',
                                columns: 2,
                                trAttrs: { style: { 'text-align': 'center' } },
                                tdAttrs: { style: { 'border': '1px solid black',  } }
                            },
                            defaults: {
                                width: "100%"
                            },
                            items: [{
                                xtype: 'displayfield',
                                value: 'Buy',
                            },{
                                xtype: 'displayfield',
                                value: 'Sell',
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirapublicchannel.companybuy}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirapublicchannel.companysell}'
                                    },
                                    cls: 'largetext',
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per g',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirapublicchannel.companybuykg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    cls: 'largetext',
                                    value: '0.00',
                                    bind: {
                                        value: '{bumirapublicchannel.companysellkg}'
                                    }
                                },{
                                    xtype: 'displayfield',
                                    cls:'boldtext',
                                    value: 'per kg',
                                }]
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'UUID',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumirapublicchannel.uuid}'
                                    },
                                }],
                                colspan: 2,
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items:[{
                                    xtype: 'displayfield',
                                    value: 'TimeStamp',
                                    cls: 'cusdisplay'
                                },{
                                    xtype: 'displayfield',
                                    value: '-',
                                    bind: {
                                        value: '{bumirapublicchannel.datetime}'
                                    },
                                }],
                                colspan: 2,
                            }]
                        }]
                    }, 
             
                    // End
                ]
        }]
    }
})