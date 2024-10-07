Ext.define('snap.view.trader.Trader', {
    extend: 'Ext.panel.Panel',
    // extend: 'snap.view.gridpanel.Base',
    xtype: 'traderview',
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
            permissions: [],
            status: '',
            intlX: [],
            intlXAU: [],
        },

    },

    initialize: function () {

        _this = this;
        vm = this.getViewModel();
        var websocket = Ext.create('Ext.ux.WebSocket', {
            // initComponent: function(){
            //     Ext.on('unmatchedroute', function (hash) {
            //         console.log("trader close2")
            //     })
            // },
            // url: 'wss://shouty10.ace2u.com/socket.io/?EIO=3&transport=websocket' ,
            url: 'wss://snappy.ace2u.com/socket.io/?EIO=3&transport=websocket',
            listeners: {
                open: function (ws) {
                    // console.log (websocket, this, ws,'The websocket is ready to use');
                },
                close: function (ws) {
                    console.log('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise('ERRRROR: ' + error);
                },
                message: function (ws, message) {

                    var tempm = parseInt(message);
                    var charx = 0;
                    if (tempm.toString() == '0' || tempm.toString() == 'NaN') {
                        return;
                    }
                    charx = tempm.toString().length
                    var msg = message.substring(charx);
                    if (msg === '') {
                        return;
                    }
                    x = Ext.JSON.decode(msg);

                    if (x[0] == 'intl') {
                        x[1].sell = '123123'
                    }
                    vm.set(x[0], x[1])
                    connection = ['intlX', 'connect', 'connect_error', 'reconnect_failed', 'reconnecting']
                    if (connection.includes(x[0])) {
                        _this.pormt_connection(x[0]);
                    }

                    if ('tradeopen' == x[0]) {
                        _this.promt_tradeopen(x[1]);
                    }
                    if ('goodfeed' == x[0]) {
                        _this.promt_goodfeed(x[1]);
                    }

                    fx = ["xaumyrx", "eurfxu", "audfxu", "nzdfxu", "gbpfxu"];
                    if (fx.includes(x[0])) {
                        _this.pormt_fxchange(x[0], x[1]);
                    }

                }
            },
        });
        // onUnmatchedRoute: function(){
        // }

        this.callParent(arguments);
    },

    listeners: {
        onUnmatchedRoute: function () {
            console.log("trader close1")
        }
    },

    pormt_fxchange: function (key_name, key_val) {
        vm = this.getViewModel();
        fxr = vm.get("fxr");
        var prefix = 'c_';
        var _return_key = prefix.concat(key_name);
        var _return = []
        if (!fxr) {
            return;
        }
        if (key_name == 'xaumyrx') {
            _return.buy = (key_val.buy / fxr.bid).toFixed(4)
            _return.sell = (key_val.sell / fxr.ask).toFixed(4)
        } else {
            _return.buy = (fxr.bid * key_val.buy).toFixed(4)
            _return.sell = (key_val.sell * fxr.ask).toFixed(4)
        }

        vm.set(_return_key, _return)
    },
    pormt_connection: function (data, num = null) {
        vm = this.getViewModel();
        if (data == 'connect' || data == 'intlX') {
            html = '<p style="color:aqua">LIVE</p>'
        }
        if (data == 'connect_error') {
            html = '<p style="color:brown">ERROR</p>'
        }
        if (data == 'reconnect_failed') {
            html = '<p style="color:red">OFFLINE</p>'
        }
        if (data == 'reconnecting') {
            html = '<p style="color:red">OFFLINE</p>'
        }
        vm.set('connection', { text: html })
    },
    promt_goodfeed: function (data) {
        vm = this.getViewModel();
        if (data == 'true') {
            html = '<p style="color:green">GOOD</p>'
        } else {
            html = '<p style="color:blue">BAD</p>'
        }
        vm.set('goodfeed', { text: html })
    },
    promt_tradeopen: function (data) {
        vm = this.getViewModel();
        if (data == 'true') {
            html = '<p style="color:green">OPEN</p>'
        } else {
            html = '<p style="color:red">CLOSE</p>'
        }
        vm.set('tradeopen', { text: html })
    },

    layout: 'fit',
    width: '100%',
    title:'Trader',
    autoScroll: true,
    bodyPadding: 5,    
    permissionRoot: '/root/trading/order',    
    cls: Ext.baseCSSPrefix + 'shadow',
    items: [{
        /* defaults: {
            width: 400,
            //height: 250,
            // bodyPadding: 10,
            autoShow: true,
            cls: ['trader-container'],
        }, */
        width: '100%',
        //height: 400,
        //cls: Ext.baseCSSPrefix + 'shadow',
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable: true,
        bodyPadding: 5,

        defaults: {
            frame: true,
            bodyPadding: 10
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
                xtype: 'panel',
                title: 'YLG',
                bodyPadding: true,
                collapsible: true,
                style: {
                    borderColor: '#204A6D',
                },
                closable: true,
                closeAction: 'hide',                               
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold', 'tradeprice-window', 'tradeprice-window'],                        
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{ylg.buy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{ylg.sell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'center',
                            pack: 'center'
                        },
                        width:'100%',
                        cls:'trader-list-item',
                        items:[{
                            xtype: 'label',
                            html: 'R Time',
                            cls: 'cusdisplay'
                        },{
                            xtype: 'label',
                            html: '-',
                            bind: {
                                html: '{ylg.timestmp}'
                            },
                            cls: 'largetext',
                        }],                                       
                    }
                ]

            },
            {
                xtype: 'panel',
                title: 'INTL',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-blue','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intl.buy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intl.sell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'center',
                            pack: 'center'
                        },
                        width:'100%',
                        cls:'trader-list-item',
                        items:[{
                            xtype: 'label',
                            html: 'R Time',
                            cls: 'cusdisplay'
                        },{
                            xtype: 'label',
                            html: '-',
                            bind: {
                                html: '{intl.timestmp}'
                            },
                            cls: 'largetext',
                        }],                                       
                    }
                ]
            },
            {
                xtype: 'panel',
                title: 'Shining Gold Bullion',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{sgbth.sgb_99spot_bid_vip2}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{sgbth.sgb_99spot_ask_vip2}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'center',
                            pack: 'center'
                        },
                        width:'100%',
                        cls:'trader-list-item',
                        items:[{
                            xtype: 'label',
                            html: 'R Time',
                            cls: 'cusdisplay'
                        },{
                            xtype: 'label',
                            html: '-',
                            bind: {
                                html: '{sgbth.time}'
                            },
                            cls: 'largetext',
                        }],                                       
                    }
                ]
            },
            {
                xtype: 'panel',
                title: 'INTL XAG',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-blue','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlXAG.buy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlXAG.sell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'center',
                            pack: 'center'
                        },
                        width:'100%',
                        cls:'trader-list-item',
                        items:[{
                            xtype: 'label',
                            html: 'R Time',
                            cls: 'cusdisplay'
                        },{
                            xtype: 'label',
                            html: '-',
                            bind: {
                                html: '{intlXAG.timestmp}'
                            },
                            cls: 'largetext',
                        }],                                       
                    }
                ]
            },
            {
                xtype: 'panel',
                title: 'MKS',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{mksp.kgBuy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{mksp.kgSell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per oz',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{mksp.taelBuy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{mksp.taelSell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'center',
                            pack: 'center'
                        },
                        width:'100%',
                        cls:'trader-list-item',
                        items:[{
                            xtype: 'label',
                            html: 'R Time',
                            cls: 'cusdisplay'
                        },{
                            xtype: 'label',
                            html: '-',
                            bind: {
                                html: '{mksp.mksdatetime}'
                            },
                            cls: 'largetext',
                        }],                                       
                    }
                ]
            },
            {
                xtype: 'panel',
                title: 'Eikon TR',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Bid',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Ask',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{fxr.bid}'
                                    },
                                    cls: 'largetext',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{fxr.ask}'
                                    },
                                    cls: 'largetext',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: 'Contributor: {fxr.fxsource}'
                                    },
                                    cls: 'largetext',
                                }]
                            }
                           
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: 'Deal Time: {fxr.dTime}'
                                    },
                                    cls: 'largetext',
                                }]
                            }
                           
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: 'R Time: {fxr.timestmp}'
                                    },
                                    cls: 'largetext',
                                }]
                            }
                           
                        ]
                    },
                ]
            },
            {
                xtype: 'panel',
                title: 'Eikon FX 6',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window', 'tradeprice-window', 'shortertable'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'RIC',
                                width: '20%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Bid',
                                width: '20%',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                html: 'Ask',
                                width: '20%',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                html: 'Src',
                                width: '25%',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                html: 'Time',
                                width: '15%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'RIC',
                                width: '20%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{fxr.bid}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{fxr.ask}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{fxr.fxsource}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{fxr.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'USD/SDG',
                                width: '20%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'USD/CNY',
                                width: '20%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnyfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnyfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnyfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnyfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'USD/IDR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'AUD/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'NZD/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'EUR/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'GBP/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'JPY/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{jpyfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{jpyfxu.sell}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{jpyfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{jpyfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'XAU/USD',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{c_xaumyrx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{c_xaumyrx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{xaumyrx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{xaumyrx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'EUR/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{c_eurfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{c_eurfxu.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu_source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{eurfxu_dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'SGD/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgdmyrx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{sgdmyrx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgdmyrx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{sgdmyrx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'CNY/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnymyrx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{cnymyrx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnymyrx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{cnymyrx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: '100 IDR/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrmyrx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{idrmyrx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrmyrx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{idrmyrx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'AUD/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{c_audfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{c_audfxu.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{audfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'NZD/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{c_nzdfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{c_nzdfxu.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{nzdfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'INR/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{inrmyrx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{inrmyrx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{inrmyrx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{inrmyrx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'GBP/MYR',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{c_gbpfxu.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{c_gbpfxu.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{gbpfxu.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border-white',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        width:'100%',
                        items: [
                            {
                                xtype: 'label',
                                html: 'MYR/JPY',
                                width: '20%',
                                cls: 'trader-list-item',
                    
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{myrjpyx.buy}'
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: "{myrjpyx.sell}"
                                },
                                width: '20%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{myrjpyx.source}'
                                },
                                width: '25%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                bind:{
                                    html: '{myrjpyx.dTime}'
                                },
                                width: '15%',
                                userCls: 'trader-list-item-grey-border',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                ]

            },
            {
                xtype: 'panel',
                title: 'Ace Open GP',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-blue','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livebuyprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per g',
                                }]
                            },
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livesellprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per g',
                                }]
                            },
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livebuyprice}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per kg',
                                }]
                            },
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livesellprice}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per kg',
                                }]
                            },
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livebuyprice_tael}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            },
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_livesellprice_tael}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            },
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: 'UUID',                                    
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    bind: {
                                        html: '{intlX.gp_uuid}'
                                    },
                                }]
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: 'Adj UUID',                                    
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    bind: {
                                        html: '{intlX.gp_adjuuid}'
                                    },
                                }]
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: 'DB Date',                                    
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: '{intlX.gp_createDate}'
                                    },
                                }]
                            }
                        ]
                    },
                    
                ]
            },
            {
                xtype: 'panel',
                title: 'Status',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: '-',
                                bind: {
                                    html: '{connection.text}, '
                                },
                                width: '33%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: '-',
                                bind: {
                                    html: '{tradeopen.text}, '
                                },
                                width: '33%',
                                cls: 'trader-list-item',
                            }, {
                                xtype: 'label',
                                html: '-',
                                bind: {
                                    html: '{goodfeed.text}'
                                },
                                width: '33%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                ]
            },
            {
                xtype: 'panel',
                title: 'Future GOLD Price',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-blue','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_rawbuyprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per g',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{intlX.gp_rawsellprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per g',
                                }]
                            }, 
                        ]
                    },
                ]
            },
            {
                xtype: 'panel',
                title: 'Historical Pricing',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-gold','tradeprice-window'],
                items: [
                    {
                        xtype: 'dataview',
                        itemTpl: 'Bid: {fx_usdmyrbuy}, Ask: {fx_usdmyrsell}, Contrib: {fx_source}, Time: {fx_dealstringtime}',
                        bind:{
                            data: "{fxlog}"
                        },
                    }
                ]
            },
            {
                xtype: 'panel',
                title: 'SGD gm (FP)',
                bodyPadding: true,
                collapsible: true,
                closable: true,
                closeAction: 'hide',
                anchor: true,
                width: '100%',
                cls: ['trader-panel','background-blue','tradeprice-window'],
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        style: { 'align': 'center' },
                        items: [
                            {
                                xtype: 'label',
                                html: 'Buy',
                                width: '50%',
                                cls: 'trader-list-item',

                            }, {
                                xtype: 'label',
                                html: 'Sell',
                                width: '50%',
                                cls: 'trader-list-item',
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{xauSGDFP.gp_fpbuyprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per gm',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{xauSGDFP.gp_fpsellprice_gm}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per gm',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: 'R Time',                                    
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: '{xauSGDFP.gp_robotdate}'
                                    },
                                }]
                            }
                            
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '50%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{xauSGDFP.gp_rawfxusdbuy}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            },
                            {
                                xtype: 'container',
                                width: '50%',
                                cls: 'trader-list-item',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                items: [{
                                    xtype: 'label',
                                    html: '0.00',
                                    bind: {
                                        html: '{xauSGDFP.gp_rawfxusdsell}'
                                    },
                                    cls: 'largetext',
                                }, {
                                    xtype: 'label',
                                    html: 'per tael',
                                }]
                            }, 
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: 'Contrib: {xauSGDFP.gp_rawfxsource}'
                                    },
                                }]
                            }
                            
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'vbox',
                                    align: 'center',
                                    pack: 'center'
                                },
                                width: '100%',
                                cls: 'trader-list-item',
                                items: [{
                                    xtype: 'label',
                                    html: '-',
                                    bind: {
                                        html: '{xauSGDFP.gp_uuid}'
                                    },
                                }]
                            }
                            
                        ]
                    },
                ]
            }

            
        ]
    }]
})