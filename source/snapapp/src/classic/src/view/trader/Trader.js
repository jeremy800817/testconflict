Ext.define('snap.view.trader.Trader',{
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
            permissions : [],
            status: '',
            intlX: [],
            intlXAU: [],
        }
    },

    createWebsocket: function(providerCode, channelName){
        var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+providerCode;
        Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl,
            //url: 'wss://gtp2.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+providerCode ,
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
                    message = JSON.parse(message);
                    //format open gp price
                    let openGP = [
                        'dealerchannel', 'onecallchannel', 'gopayzchannel', 'onecentchannel', 
                        'mkschannel', 'mibchannel', 'poschannel', 'toyyibpaychannel',
                        'hopegoldchannel', 'semuapaychannel', 'nusagoldchannel', 'annurgoldchannel',
                        'wavpaygoldchannel',
                    ];
                    let formatDecimal = 3;
                    //if (openGP.includes(channelName)) {
                        message.data[0].companybuy = _this.formatPrice(message.data[0].companybuy, formatDecimal);
                        message.data[0].companysell = _this.formatPrice(message.data[0].companysell, formatDecimal);
                    //}
                    //format price color
                    if (vm.get(channelName)) {
                        Object.keys(message.data[0]).map(function(key, index) {
                            let fields = [
                                'companybuy', 'companysell'
                            ];
                            if(fields.includes(key)){
                                if (vm.get(channelName)[key]) {
                                    message.data[0][key] = _this.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);
                                }
                            }
                        });
                    }

                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    let dateTime = new Date(message.data[0].timestamp * 1000.00);
                    var dateTimeStr =
                        dateTime.getFullYear() + "-" +
                        ("00" + (dateTime.getMonth() + 1)).slice(-2) + "-" +
                        ("00" + dateTime.getDate()).slice(-2) + " " +
                    
                        ("00" + dateTime.getHours()).slice(-2) + ":" +
                        ("00" + dateTime.getMinutes()).slice(-2) + ":" +
                        ("00" + dateTime.getSeconds()).slice(-2);
                    message.data[0].datetime = dateTimeStr;
                    vm.set(channelName, message.data[0]);
                }
            },
        });
    },

    initComponent: function(){
        env = snap.getApplication().info.env
        core_socket = '';
        if (env == 'prod'){
            core_socket = 'wss://gungho.ace2u.com:8806/socket.io/?EIO=3&transport=websocket';
        }
        if (env == 'dev'){
            core_socket = 'wss://shouty20.ace2u.com:8806/socket.io/?EIO=3&transport=websocket';
        }
        _this = this;
        vm = this.getViewModel();
        var websocket = Ext.create ('Ext.ux.WebSocket', {
            // initComponent: function(){
            //     Ext.on('unmatchedroute', function (hash) {
            //         console.log("trader close2")
            //     })
            // },
            url: core_socket,
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
                    
                    /*var tempm = parseInt(message);
                    var charx = 0;
                    if (tempm.toString() == '0' || tempm.toString() == 'NaN'){
                        return;
                    }
                    charx = tempm.toString().length
                    var msg = message.substring(charx);
                    if (msg === ''){
                        return;
                    }
                    x = Ext.JSON.decode(msg);*/

                    let result = message.match(/\[.*\]$/g);
                    if (null === result) {
                        return;
                    }
                    
                    let x = Ext.JSON.decode(result[0]);
                    let y = Ext.JSON.decode(result[0]);

                    //check date time
                    Object.keys(y[1]).map(function(key, index) {
                        let dateTimeField = ['mksdatetime', 'timestmp', 'gp_robotdate'];
                        if(dateTimeField.includes(key)){
                            let matches = y[1][key].match(/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/); 
                            if (null === matches) {
                                y[1][key] = '-';
                            }
                        }
                    });

                    //mks format price
                    if ('mksp' == y[0]) {
                        y[1]['kgBuy'] = Ext.util.Format.number(parseFloat(y[1]['kgBuy'].replace(/,/g, '')), '0,000.000');
                        y[1]['kgSell'] = Ext.util.Format.number(parseFloat(y[1]['kgSell'].replace(/,/g, '')), '0,000.000');
                    }

                    if (vm.get(y[0])) {
                        Object.keys(y[1]).map(function(key, index) {
                            let fields = [
                                'companybuy', 'companysell',
                                'bid', 'ask', 
                                'gp_rawbuyprice_gm', 'gp_rawsellprice_gm', 
                                'buy', 'sell',
                                'gp_fpbuyprice_gm', 'gp_fpsellprice_gm', 'gp_rawfxusdbuy', 'gp_rawfxusdsell',
                                'kgBuy', 'kgSell', 'taelBuy', 'taelSell',
                                'gp_livebuyprice_gm', 'gp_livesellprice_gm', 'gp_livebuyprice', 'gp_livesellprice', 'gp_livebuyprice_tael', 'gp_livesellprice_tael',
                            ];
                            if(fields.includes(key)){
                                if (vm.get(y[0])[key]) {
                                    y[1][key] = _this.formatPriceColor(y[1][key], vm.get(y[0])[key]);
                                }
                            }
                        });
                    }

                    vm.set(y[0], y[1]);

                    connection = ['intlX', 'connect', 'connect_error', 'reconnect_failed', 'reconnecting'];
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
        
        this.createWebsocket('INTLX.GTP_T1','gtpt1channel');
        this.createWebsocket('INTLX.GTP_T2','gtpt2channel');
        this.createWebsocket('INTLX.MIGA','mibchannel');
        this.createWebsocket('INTLX.CW','dealerchannel');
        this.createWebsocket('INTLX.MKS','mkschannel');
        this.createWebsocket('INTLX.POS','poschannel');
        this.createWebsocket('INTLX.ONECALL','onecallchannel');
        this.createWebsocket('INTLX.ONECENT','onecentchannel');
        this.createWebsocket('INTLX.MGOLD','mgoldchannel');
        this.createWebsocket('INTLX.GOGOLD','gopayzchannel');

        this.createWebsocket('INTLX.TOYYIBPAY','toyyibpaychannel');
        this.createWebsocket('INTLX.HOPEGOLD','hopegoldchannel');
        this.createWebsocket('INTLX.SEMUAPAY','semuapaychannel');
        this.createWebsocket('INTLX.NUSAGOLD','nusagoldchannel');
        this.createWebsocket('INTLX.EASIGOLD','easigoldchannel');
        this.createWebsocket('INTLX.ANNUARGOLD','annurgoldchannel');
        this.createWebsocket('INTLX.WAVPAY','wavpaygoldchannel');

        this.callParent(arguments);
    },
    
    listeners: {
        onUnmatchedRoute: function(){
            console.log("trader close1")
        }
    },

    formatPrice: function(price, decimal){
        price = parseFloat(price);
        return price.toFixed(decimal);
    },

    formatPriceColor: function(newPrice, oldPrice){
        newPrice = newPrice.toString();
        oldPrice = oldPrice.toString();

        let result = oldPrice.match(/\<span.*\>(.*)\<\/span\>/);

        if (result) {
            oldPrice = result[1];
        }
        
        if (newPrice > oldPrice) {
            return '<span style="color:green;">'+newPrice+'</span>';
        }
        if (newPrice < oldPrice) {
            return '<span style="color:red;">'+newPrice+'</span>';
        }
        if (newPrice == oldPrice) {
            return newPrice;
        }
    },

    pormt_fxchange: function(key_name, key_val){
        vm = this.getViewModel();
        fxr = vm.get("fxr");
        var prefix = 'c_';
        var _return_key = prefix.concat(key_name);
        var _return = [];
        if (!fxr){
            return;
        }
        if (key_name == 'xaumyrx'){
            _return.buy = (key_val.buy / fxr.bid).toFixed(4);
            _return.sell = (key_val.sell / fxr.ask).toFixed(4);
        }else{
            _return.buy = (fxr.bid * key_val.buy).toFixed(4);
            _return.sell = (key_val.sell * fxr.ask).toFixed(4);
        }

        if (vm.get(_return_key)) {
            Object.keys(_return).map(function(key, index) {
                _return[key] = _this.formatPriceColor(_return[key], vm.get(_return_key)[key]);
            });
        }

        vm.set(_return_key, _return);
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

   
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',
    scrollable: true,

    items: {
        xtype: 'container',
        items: [
            {
                xtype: 'trader_dashboard'
            }
        ]
    }
})

//dashboard items settings
var defaultSrollable = true;

//cw_open_gp
Ext.define('snap.view.trader.Trader.cw_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'cw_open_gp',

    scrollable: defaultSrollable,
    
    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{dealerchannel.companybuy}'
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
                    value: '{dealerchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{dealerchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
})

//onecall_open_gp
Ext.define('snap.view.trader.Trader.onecall_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'onecall_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{onecallchannel.companybuy}'
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
                    value: '{onecallchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{onecallchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//eikon_trp
Ext.define('snap.view.trader.Trader.eikon_trp', {
    extend: 'Ext.panel.Panel',
    xtype: 'eikon_trp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
            value: 'Bid',
        },{
            xtype: 'displayfield',
            value: 'Ask',
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
                    value: '{fxr.bid}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'USD',
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
                    value: '{fxr.ask}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'USD',
            }]
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: 'Contributor: {fxr.fxsource}'
            },
            colspan: 2,
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: 'Deal Time: {fxr.dTime}'
            },
            colspan: 2,
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: 'R Time: {fxr.timestmp}'
            },
            colspan: 2,
        }]
    }]
});

//eikon_fx6
Ext.define('snap.view.trader.Trader.eikon_fx6', {
    extend: 'Ext.panel.Panel',
    xtype: 'eikon_fx6',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
        layout: {
            type:'table',
            columns: 5,
            trAttrs: { style: { 'text-align': 'center' } },
            tdAttrs: { style: { 'border': '1px solid black',  } }
        },
        defaults: {
            width: "100%"
        },
        items: [{
            xtype: 'displayfield',
            value: 'RIC',
        },{
            xtype: 'displayfield',
            cls: 'boldtext',
            value: 'Bid',
        },{
            xtype: 'displayfield',
            cls: 'boldtext',
            value: 'Ask',
        },{
            xtype: 'displayfield',
            value: 'Src',
        },{
            xtype: 'displayfield',
            value: 'Time',
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'USD/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{fxr.bid}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{fxr.ask}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{fxr.fxsource}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{fxr.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'USD/SDG',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{sgfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{sgfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{sgfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{sgfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'USD/CNY',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{cnyfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{cnyfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{cnyfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{cnyfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'USD/IDR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{idrfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{idrfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{idrfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{idrfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'AUD/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{audfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{audfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{audfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{audfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'NZD/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{nzdfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{nzdfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{nzdfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{nzdfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'EUR/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{eurfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{eurfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{eurfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{eurfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'GBP/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gbpfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gbpfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{gbpfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{gbpfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'JPY/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{jpyfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{jpyfxu.sell}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{jpyfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{jpyfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'XAU/USD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{c_xaumyrx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{c_xaumyrx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{xaumyrx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{xaumyrx.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'EUR/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{c_eurfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{c_eurfxu.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{eurfxu_source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{eurfxu_dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'SGD/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{sgdmyrx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{sgdmyrx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{sgdmyrx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{sgdmyrx.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'CNY/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{cnymyrx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{cnymyrx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{cnymyrx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{cnymyrx.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: '100 IDR/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{idrmyrx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{idrmyrx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{idrmyrx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{idrmyrx.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'AUD/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{c_audfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{c_audfxu.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{audfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{audfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'NZD/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{c_nzdfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{c_nzdfxu.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{nzdfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{nzdfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'INR/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{inrmyrx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{inrmyrx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{inrmyrx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{inrmyrx.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'GBP/MYR',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{c_gbpfxu.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{c_gbpfxu.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{gbpfxu.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{gbpfxu.dTime}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'MYR/JPY',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{myrjpyx.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{myrjpyx.sell}"
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{myrjpyx.source}'
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind:{
                value: '{myrjpyx.dTime}'
            }
        },
        ]
    }]
});

//historical_pricing
Ext.define('snap.view.trader.Trader.historical_pricing', {
    extend: 'Ext.panel.Panel',
    xtype: 'historical_pricing',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'dataview',
        itemTpl: 'Bid: {fx_usdmyrbuy}, Ask: {fx_usdmyrsell}, Contrib: {fx_source}, Time: {fx_dealstringtime}',
        bind:{
            data: "{fxlog}"
        },
    }]
});

//gogold_open_gp
Ext.define('snap.view.trader.Trader.gogold_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'gogold_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{gopayzchannel.companybuy}'
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
                    value: '{gopayzchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{gopayzchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//onecent_open_gp
Ext.define('snap.view.trader.Trader.onecent_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'onecent_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{onecentchannel.companybuy}'
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
                    value: '{onecentchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{onecentchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//future_gold_price_inltx_socket
Ext.define('snap.view.trader.Trader.future_gold_price_inltx_socket', {
    extend: 'Ext.panel.Panel',
    xtype: 'future_gold_price_inltx_socket',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
            cls: 'boldtext',
            value: 'Buy',
        },{
            xtype: 'displayfield',
            cls: 'boldtext',
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
                cls: 'largetext',
                value: '0.00',
                bind: {
                    value: '{intlX.gp_rawbuyprice_gm}'
                }
            },{
                xtype: 'displayfield',
                cls: 'boldtext',
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
                    value: '{intlX.gp_rawsellprice_gm}'
                }
            },{
                xtype: 'displayfield',
                cls: 'boldtext',
                value: 'per g',
            }]
        }]
    }]
});

//mks_open_gp
Ext.define('snap.view.trader.Trader.mks_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'mks_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{mkschannel.companybuy}'
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
                    value: '{mkschannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{mkschannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//mib_open_gp
Ext.define('snap.view.trader.Trader.mib_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'mib_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{mibchannel.companybuy}'
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
                    value: '{mibchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{mibchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//intl
Ext.define('snap.view.trader.Trader.intl', {
    extend: 'Ext.panel.Panel',
    xtype: 'intl',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{intl.buy}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per oz',
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
                // renderer: Ext.util.Format.numberRenderer('0.0000000'),
                bind: {
                    value: '{intl.sell}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per oz',
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
                value: 'R Time',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{intl.timestmp}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//pos_open_gp
Ext.define('snap.view.trader.Trader.pos_open_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'pos_open_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{poschannel.companybuy}'
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
                    value: '{poschannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{poschannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//mks
Ext.define('snap.view.trader.Trader.mks', {
    extend: 'Ext.panel.Panel',
    xtype: 'mks',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{mksp.kgBuy}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per oz',
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
                    value: '{mksp.kgSell}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per oz',
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
                    value: '{mksp.taelBuy}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per tael',
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
                    value: '{mksp.taelSell}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
                value: 'per tael',
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
                value: 'R Time',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{mksp.mksdatetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//sgd_gm_fp
Ext.define('snap.view.trader.Trader.sgd_gm_fp', {
    extend: 'Ext.panel.Panel',
    xtype: 'sgd_gm_fp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                cls: 'largetext',
                value: '0.00',
                bind: {
                    value: '{xauSGDFP.gp_fpbuyprice_gm}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per gm',
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
                    value: '{xauSGDFP.gp_fpsellprice_gm}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per gm',
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
                value: 'R Time',
                cls: ['cusdisplay','boldtext']
            },{
                xtype: 'displayfield',
                cls: 'largetext',
                value: '-',
                bind: {
                    value: '{xauSGDFP.gp_robotdate}'
                },
                cls: 'largetext2',
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
                cls: 'largetext',
                value: '0.00',
                bind: {
                    value: '{xauSGDFP.gp_rawfxusdbuy}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per tael',
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
                    value: '{xauSGDFP.gp_rawfxusdsell}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per tael',
            }]
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: 'Contrib: {xauSGDFP.gp_rawfxsource}'
            },
            colspan: 2,
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: '{xauSGDFP.gp_uuid}'
            },
            colspan: 2,
        }]
    }]
});

//ace_open_gp_inltx_socket
Ext.define('snap.view.trader.Trader.ace_open_gp_inltx_socket', {
    extend: 'Ext.panel.Panel',
    xtype: 'ace_open_gp_inltx_socket',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{intlX.gp_rawbuyprice_gm}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
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
                    value: '{intlX.gp_rawsellprice_gm}'
                },
                cls: 'largetext',
            },{
                xtype: 'displayfield',
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
                    value: '{intlX.gp_livebuyprice}'
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
                    value: '{intlX.gp_livesellprice}'
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
                    value: '{intlX.gp_livebuyprice_tael}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per tael',
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
                    value: '{intlX.gp_livesellprice_tael}'
                }
            },{
                xtype: 'displayfield',
                cls:'boldtext',
                value: 'per tael',
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
                value: 'DB Date',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{intlX.gp_createDate}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//mgold
Ext.define('snap.view.trader.Trader.mgold', {
    extend: 'Ext.panel.Panel',
    xtype: 'mgold',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{mgoldchannel.companybuy}'
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
                    value: '{mgoldchannel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{mgoldchannel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//gtp_t1_gp
Ext.define('snap.view.trader.Trader.gtp_t1_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'gtp_t1_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{gtpt1channel.companybuy}'
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
                    value: '{gtpt1channel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{gtpt1channel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//gtp_t2_gp
Ext.define('snap.view.trader.Trader.gtp_t2_gp', {
    extend: 'Ext.panel.Panel',
    xtype: 'gtp_t2_gp',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
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
                    value: '{gtpt2channel.companybuy}'
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
                    value: '{gtpt2channel.companysell}'
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
                value: 'TimeStamp',
                cls: 'cusdisplay'
            },{
                xtype: 'displayfield',
                value: '-',
                bind: {
                    value: '{gtpt2channel.datetime}'
                },
                cls: 'largetext2',
            }],
            colspan: 2,
        }]
    }]
});

//status
Ext.define('snap.view.trader.Trader.status', {
    extend: 'Ext.panel.Panel',
    xtype: 'status',

    scrollable: defaultSrollable,

    items: [{
        type: 'container',
        layout: "hbox",
        defaults: {
            padding: "0 5px",
            height: '30px'
        },
        items: [{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: '{connection.text}, '
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: '{tradeopen.text}, '
            }
        },{
            xtype: 'displayfield',
            value: '-',
            bind: {
                value: '{goodfeed.text}'
            }
        }]
    }]
});

//buy & sell
Ext.define('snap.view.trader.Trader.all_trader',{
    extend: 'Ext.panel.Panel',
    xtype: 'all_trader',

    scrollable: defaultSrollable,

    items: [{
        xtype: 'container',
        padding: '5px',
        layout: {
            type:'table',
            columns: 3,
            trAttrs: { style: { 'text-align': 'center' } },
            tdAttrs: { style: { 'border': '1px solid black',  } }
        },
        defaults: {
            width: "100%"
        },
        items: [{
            xtype: 'displayfield',
            value: 'Name',
        },{
            xtype: 'displayfield',
            cls:'boldtext',
            value: 'Buy',
        },{
            xtype: 'displayfield',
            cls:'boldtext',
            value: 'Sell',
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'CW Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{dealerchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{dealerchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'GoGold Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gopayzchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gopayzchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'MKS Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mkschannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mkschannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'POS Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{poschannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{poschannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'MGOLD',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mgoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mgoldchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'OneCall Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{onecallchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{onecallchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'OneCENT Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{onecentchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{onecentchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'MIB Open GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mibchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mibchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'MKS(per kg)',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mksp.kgBuy}'
            },
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{mksp.kgSell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'GTP T1 GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gtpt1channel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{gtpt1channel.companysell}"
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'GTP T2 GP',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{gtpt2channel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{gtpt2channel.companysell}"
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'Toyyib Pay',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{toyyibpaychannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{toyyibpaychannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'Hope Gold',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{hopegoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{hopegoldchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'SemuaPay',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{semuapaychannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{semuapaychannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'Nusa Gold',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{nusagoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{nusagoldchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'Easi Gold',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{easigoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{easigoldchannel.companysell}'
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'Annur Gold',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{annurgoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{annurgoldchannel.companysell}'
            }
        },
         //row---
         {
            xtype: 'displayfield',
            value: 'Wavpay Gold',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{wavpaygoldchannel.companybuy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{wavpaygoldchannel.companysell}'
            }
        },
        //row---
        /*{
            xtype: 'displayfield',
            value: 'Future Gold Price - intlX(socket)',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{intlX.gp_rawbuyprice_gm}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{intlX.gp_rawsellprice_gm}"
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'INTL',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{intl.buy}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{intl.sell}"
            }
        },
        //row---
        {
            xtype: 'displayfield',
            value: 'SGD gm (FP)',
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: '{xauSGDFP.gp_fpbuyprice_gm}'
            }
        },{
            xtype: 'displayfield',
            cls: 'largetext',
            value: '-',
            bind:{
                value: "{xauSGDFP.gp_fpsellprice_gm}"
            }
        }*/]
    }]
});

//dashboard settings
var dashboardHeight = (937 - 59 - 17);
var dashboardWidth = (1920 - 250);
var panelHeight = 165;
var defaultPanelHeader = {
    height: 30,
    padding: '0 10 0 10'
};
var defaultClosable = false;

//dashboard
Ext.define('snap.view.trader.Trader.Dashboard', {
    extend: 'Ext.dashboard.Dashboard',
    xtype: 'trader_dashboard',

    requires: [
        'Ext.dashboard.Dashboard',
        'snap.util.HttpStateProvider'
    ],
    
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },

    //window inner height - top  - bottom
    height: dashboardHeight,
    //window inner width - leftbar menu
    width: dashboardWidth,
    maxColumns: 5,
    scrollable: true,
    columnWidths: [
        0.20,
        0.25,
        0.30,
        0.25,
    ],

    //parts
    parts: {
        cw_open_gp: {
            viewTemplate: {
                title: 'CW Open GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'cw_open_gp',
                }],
            }
        },
        onecall_open_gp: {
            viewTemplate: {
                title: 'OneCALL Open GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'onecall_open_gp',
                }],
            }
        },
        eikon_trp: {
            viewTemplate: {
                title: 'Eikon TR',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'eikon_trp',
                }],
            }
        },
        eikon_fx6: {
            viewTemplate: {
                title: 'Eikon FX 6',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window','tradeprice-window2'],
                items: [{
                    xtype: 'eikon_fx6',
                }],
            }
        },
        historical_pricing: {
            viewTemplate: {
                title: 'Historical Pricing',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'historical_pricing',
                }],
                style: {
                    lineHeight: '16.0px'
                }
            }
        },
        gogold_open_gp: {
            viewTemplate: {
                title: 'GoGold Open Gp',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'gogold_open_gp',
                }],
            }
        },
        onecent_open_gp: {
            viewTemplate: {
                title: 'OneCENT Open Gp',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'onecent_open_gp',
                }],
            }
        },
        future_gold_price_inltx_socket: {
            viewTemplate: {
                title: 'Future Gold Price - intlX(socket)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'future_gold_price_inltx_socket',
                }],
            }
        },
        mks_open_gp: {
            viewTemplate: {
                title: 'MKS Open GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'mks_open_gp',
                }],
            }
        },
        mib_open_gp: {
            viewTemplate: {
                title: 'MIB Open GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'mib_open_gp',
                }],
            }
        },
        intl: {
            viewTemplate: {
                title: 'INTL',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'intl',
                }],
            }
        },
        pos_open_gp: {
            viewTemplate: {
                title: 'POS Open GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'pos_open_gp',
                }],
            }
        },
        mks: {
            viewTemplate: {
                title: 'MKS',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'mks',
                }],
            }
        },
        sgd_gm_fp: {
            viewTemplate: {
                title: 'SGD gm (FP)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'sgd_gm_fp',
                }],
            }
        },
        ace_open_gp_inltx_socket: {
            viewTemplate: {
                title: 'Future Gold Price - intlX(socket)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'ace_open_gp_inltx_socket',
                }],
            }
        },
        mgold: {
            viewTemplate: {
                title: 'MGOLD',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'mgold',
                }],
            }
        },
        gtp_t1_gp: {
            viewTemplate: {
                title: 'GTP T1 GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'gtp_t1_gp',
                }],
            }
        },
        gtp_t2_gp: {
            viewTemplate: {
                title: 'GTP T2 GP',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'gtp_t2_gp',
                }],
            }
        },
        status: {
            viewTemplate: {
                title: 'Status',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-gold','tradeprice-window'],
                items: [{
                    xtype: 'status',
                }],
            }
        },
        all_trader: {
            viewTemplate: {
                title: 'Gold Price (Buy & Sell)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                cls: ['background-blue','tradeprice-window'],
                items: [{
                    xtype: 'all_trader',
                }],
            }
        },
    },
    defaultContent: [
        /*{
            type: 'cw_open_gp',
            columnIndex: 0,
            height: panelHeight,
        },
        {
            type: 'onecall_open_gp',
            columnIndex: 1,
            height: panelHeight,
        },*/
        {
            type: 'future_gold_price_inltx_socket',
            columnIndex: 0,
            height: 120,
        },
        {
            type: 'intl',
            columnIndex: 0,
            height: 165,
        },
        {
            type: 'sgd_gm_fp',
            columnIndex: 0,
            height: 266,
        },
        {
            type: 'all_trader',
            columnIndex: 1,
            height: 850,
        },
        {
            type: 'eikon_fx6',
            columnIndex: 2,
            height: 850,
        },
        {
            type: 'historical_pricing',
            columnIndex: 3,
            height: 850,
        },
        /*{
            type: 'gogold_open_gp',
            columnIndex: 0,
            height: panelHeight,
        },
        {
            type: 'onecent_open_gp',
            columnIndex: 1,
            height: panelHeight,
        },
        {
            type: 'mks_open_gp',
            columnIndex: 0,
            height: panelHeight,
        },
        {
            type: 'mib_open_gp',
            columnIndex: 1,
            height: panelHeight,
        },
        {
            type: 'pos_open_gp',
            columnIndex: 0,
            height: panelHeight,
        },
        {
            type: 'mks',
            columnIndex: 1,
            height: panelHeight,
        },
        */
        /*{
            type: 'ace_open_gp_inltx_socket',
            columnIndex: 0,
            height: panelHeight,
        },*/
        /*{
            type: 'mgold',
            columnIndex: 0,
            height: panelHeight,
        },
        {
            type: 'gtp_t1_gp',
            columnIndex: 1,
            height: panelHeight,
        },
        {
            type: 'gtp_t2_gp',
            columnIndex: 2,
            height: panelHeight,
        },*/
        {
            type: 'eikon_trp',
            columnIndex: 0,
            height: 201,
        },
        {
            type: 'status',
            columnIndex: 0,
            height: 50,
        },
    ]    
})