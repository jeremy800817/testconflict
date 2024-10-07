Ext.define('snap.view.trader.TraderOrdersKTP', {
    extend: 'Ext.panel.Panel',
    xtype: 'traderordersktpview',
    requires: [
        'snap.util.HttpStateProvider',
        'Ext.ZIndexManager'
        // 'Ext.layout.container.Fit',
        // // 'snap.view.orderdashboard.OrderDashboardController',
        // 'snap.view.trader.TraderModel',
        // 'snap.store.TraderPrice',

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

    stateful: true,

    initComponent: function () {
        console.log(Ext.getClass(this).getName(),'Ext.getClass(this).getName()',this.stateId)
        this.stateId = this.stateId || Ext.getClass(this).getName();
        
        _this = this;
        vm = this.getViewModel();

        var timerCheckStore = function(store){
            if (store){
                store.load()
            }
        };

        // vm = this.up('traderordersview').viewModel;
        var countdown = 10;
        var timer = setInterval(function(){
            if (countdown <= 0){
                // timerCheckStore(Ext.getStore('traderorders'))
                // timerCheckStore(Ext.getStore('traderorders1'))
                // timerCheckStore(Ext.getStore('traderorders2'))
                // timerCheckStore(Ext.getStore('traderorders3'))
                // timerCheckStore(Ext.getStore('traderorders4'))
                // timerCheckStore(Ext.getStore('traderorders5'))
                // timerCheckStore(Ext.getStore('traderorderspos01'))
                // // timerCheckStore(Ext.getStore('traderorderspos02'))
                // timerCheckStore(Ext.getStore('traderordersmib'))
                // timerCheckStore(Ext.getStore('traderfutureordersmib'))
                // timerCheckStore(Ext.getStore('traderfutureordersmibsummary'))
                // timerCheckStore(Ext.getStore('traderordersgogoldsummary'))
                timerCheckStore(Ext.getStore('traderorderspkbgoldsummary'))
                timerCheckStore(Ext.getStore('traderordersbumiragoldsummary'))
                countdown = 10;
            }
            vm.set('countdowntext', countdown);
            countdown -= 1;
        }, 1000)

        this.callParent(arguments);
    },
    

    

    storeId: "TraderStoreAl",

    formDialogWidth: 950,
    // permissionRoot: '/root/trading/ktporder',
    formDialogWidth: 950,
    permissionRoot: '/root/system/trader/ktplist',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    stateful: true,

    items: {
        xtype: 'container',
        layout: "fit",
        height: 1000,
        defaults: {
            width: 400,
            height: 250,
            // bodyPadding: 10,
            autoShow: true,
            cls: [],
        },
        
        items: [
            {
                xtype: "panel",
                tbar: [
                    {
                        // xtype: 'button', // default for Toolbars
                        text: 'Refresh',
                        handler: function(){
                            var timerCheckStore = function(store){
                                if (store){
                                    store.load()
                                }
                            };
                            
                            // timerCheckStore(Ext.getStore('traderorders'))
                            // timerCheckStore(Ext.getStore('traderorders1'))
                            // timerCheckStore(Ext.getStore('traderorders2'))
                            // timerCheckStore(Ext.getStore('traderorders4'))
                            // timerCheckStore(Ext.getStore('traderorders5'))
                            // timerCheckStore(Ext.getStore('traderorderspos01'))
                            // // timerCheckStore(Ext.getStore('traderorderspos02'))
                            // timerCheckStore(Ext.getStore('traderordersmib'))
                            // timerCheckStore(Ext.getStore('traderfutureordersmib'))
                            // timerCheckStore(Ext.getStore('traderfutureordersmibsummary'))
                            // timerCheckStore(Ext.getStore('traderordersgogoldsummary'))
                            timerCheckStore(Ext.getStore('traderorderspkbgoldsummary'))
                            timerCheckStore(Ext.getStore('traderordersbumiragoldsummary'))
                        },
                    },
                    {
                        xtype: 'tbtext',
                        bind:  {
                            text: '{countdowntext}s'
                        }
                    },
                ]
            },
            {
                xtype: 'trader_orders_pkbgoldordertotal',
            },
            {
                xtype: 'trader_orders_bumiragoldordertotal',
            },
         
            // {
            //     xtype: 'trader_orders_todaysalestotal'
            // },
            // {
            //     xtype: 'trader_orders_bookingorders',
                
            // },
            // {
            //     xtype: 'trader_orders_exportorders',
                
            // },
            // {
            //     xtype: 'trader_orders_customerqueuetosell',
                
            // },
            // {
            //     xtype: 'trader_orders_customerqueuetobuy',
                
            // },

            // {
            //     xtype: 'trader_orders_posbuybacktotal',
                
            // },

            // {
            //     xtype: 'trader_orders_mibordertotal',
                
            // },

            // {
            //     xtype: 'trader_orders_mibfutureordertotal',
                
            // },

            // {
            //     xtype: 'trader_orders_gogoldordertotal',
                
            // },

        ]
    }
})

// example template
// Ext.define('snap.view.trader.TraderOrders.',{
//     extend: 'Ext.window.Window',
//     xtype: '',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
// })

// Ext.define('snap.view.trader.TraderOrders.trader_orders_todaysalestotal',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_todaysalestotal',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: false,
//     title: 'Today Sales Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 0, y: 45,
//     width: 700,
//     height: 400,
//     items: [{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders",
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120 },
//             { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130 },
//         ]
//     },{
//         style: {"marginTop": '20px'},
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders1"
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120 },
//             { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130 },
//             { text: 'Product', dataIndex: 'pdt_name', filter: { type: 'string' }, width: 130 },
//         ],
//     }],
// })
// Ext.define('snap.view.trader.TraderOrders.trader_orders_bookingorders',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_bookingorders',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: false,
//     title: 'Booking Orders',
//     headerPosition: 'top',
//     constrain: true,
//     x: 0, y: 445,
//     width: 700,
//     height: 350,
//     items: [{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders2"
//         },
//         columns: [
//             { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
//             { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
//             { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
//             { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
//             { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
//             { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
//             { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
//             { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
//             { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
//             { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }]
// })
// Ext.define('snap.view.trader.TraderOrders.trader_orders_exportorders',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_exportorders',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: false,
//     title: 'Export Orders',
//     headerPosition: 'top',
//     constrain: true,
//     x: 250, y: 445,
//     width: 700,
//     height: 350,
//     items: [{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders5"
//         },
//         columns: [
//             { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
//             { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
//             { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
//             { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
//             { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
//             { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
//             { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
//             { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
//             { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
//             { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }]
// })
// Ext.define('snap.view.trader.TraderOrders.trader_orders_customerqueuetosell',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_customerqueuetosell',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: false,
//     title: 'Customer Queue to Sell',
//     headerPosition: 'top',
//     constrain: true,
//     x: 700, y: 0,
//     width: 700,
//     height: 300,
//     items: [{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders3",
//         },
//         columns: [
//             { text: 'GTP#', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Time', dataIndex: '', filter: { type: 'string' }, width: 120 },
//             { text: 'Product', dataIndex: '', filter: { type: 'string' }, width: 150 },
//             { text: 'Book by', dataIndex: '', filter: { type: 'string' }, width: 150 },
//             { text: 'Weight', dataIndex: '', filter: { type: 'string' }, width: 130 },
//             { text: 'Ask Price', dataIndex: '', filter: { type: 'string' }, width: 130 },
//             { text: 'Refine Fee', dataIndex: '', filter: { type: 'string' }, width: 130 },
//             { text: 'Final', dataIndex: '', filter: { type: 'string' }, width: 130 },
//             { text: 'Value', dataIndex: '', filter: { type: 'string' }, width: 130 },
//             { text: 'Customer', dataIndex: '', filter: { type: 'string' }, width: 130 },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }]
// })
// Ext.define('snap.view.trader.TraderOrders.trader_orders_customerqueuetobuy',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_customerqueuetobuy',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: false,
//     title: 'Customer Queue to Buy',
//     headerPosition: 'top',
//     constrain: true,
//     x: 700, y: 200,
//     width: 700,
//     height: 300,
//     items: [{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderOrders4",
//             // params: {
//             //     start: 0,
//             //     limit: itemsPerPage
//             // }
//         },
//         columns: [
//             { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
//             { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
//             { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
//             { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
//             { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
//             { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
//             { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
//             { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
//             { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
//             { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }]
// })        
// Ext.define('snap.view.trader.TraderOrders.trader_orders_posbuybacktotal',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_posbuybacktotal',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: true,
//     title: 'POS Buyback Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 0, y: 45,
//     width: 700,
//     height: 300,
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderOrdersPOS01",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right' },
//             { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130 },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// })        
// Ext.define('snap.view.trader.TraderOrders.trader_orders_mibordertotal',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_mibordertotal',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: true,
//     title: 'MIB Order Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 0, y: 345,
//     width: 700,
//     height: 300,
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderOrdersMib",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 },
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//                 editor: {
//                     xtype: 'numberfield',
//                     decimalPrecision: 3
//                 },
//             },
//             { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// })        
// Ext.define('snap.view.trader.TraderOrders.trader_orders_mibfutureordertotal',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_mibfutureordertotal',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: true,
//     title: 'MIB Future Order Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 700, y: 345,
//     width: 700,
//     height: 450,
//     // stateful: {
//     //     width: true,
//     //     height: true,
//     // },
//     stateful: true,
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderFutureOrdersMibSummary",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.ordertype == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.ordertype == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.ordertype == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.ordertype == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//                 editor: {
//                     xtype: 'numberfield',
//                     decimalPrecision: 3
//                 }
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.ordertype == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.ordertype == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Order Type', dataIndex: 'ordertype', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     },{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderFutureOrdersMib",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'orq_createdon', filter: { type: 'string' }, },
//             { text: 'Order Price Target', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Total Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')
//             },
//             { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// })     
Ext.define('snap.view.trader.TraderOrders.trader_orders_pkbgoldordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_pkbgoldordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'PKBGold Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 45,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersPkbgoldSummary",
            pageSize: 5,
        },
        columns: [
            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Fee', dataIndex: 'total_fee', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }],
})  

Ext.define('snap.view.trader.TraderOrders.trader_orders_bumiragoldordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_bumiragoldordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'BumiraGold Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersBumiragoldSummary",
            pageSize: 5,
        },
        columns: [
            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.type == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.type == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Fee', dataIndex: 'total_fee', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }],
})  
// Ext.define('snap.view.trader.TraderOrders.trader_orders_gogoldordertotal',{
//     extend: 'Ext.window.Window',
//     xtype: 'trader_orders_gogoldordertotal',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     autoShow: true,
//     title: 'GoGold Order Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 0, y: 345,
//     width: 700,
//     height: 300,
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderOrdersGogoldSummary",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
//                 }, 
//             },
//             { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//             { text: 'Total Fee', dataIndex: 'total_fee', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// })
// Ext.define('snap.view.trader.TraderOrders.smallwindow1',{
//     extend: 'Ext.window.Window',
//     xtype: 'smallwindow1',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     autoShow: true,
//     title: 'MIB Future Order Total',
//     headerPosition: 'top',
//     constrain: true,
//     x: 700, y: 345,
//     width: 700,
//     height: 450,
//     // alwaysOnTop: 5,
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();
//         this.callParent(arguments); 
//     },
//     listeners: {
//         'activate': function(){
//             console.log('activate',this)
//             this.setStyle('color: red')
//         },
//     },
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderFutureOrdersMibSummary",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Order Type', dataIndex: 'ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right' },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     },{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderFutureOrdersMib",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'orq_createdon', filter: { type: 'string' }, },
//             { text: 'Order Price Target', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Total Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//             },
//             { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// },)

// Ext.define('snap.view.trader.TraderOrders.smallwindow2',{
//     extend: 'Ext.window.Window',
//     xtype: 'smallwindow2',
//     requires: [
//         'snap.util.HttpStateProvider'
//     ],
//     autoShow: true,
//     title: 'MIB Future Order Total222222222222222',
//     headerPosition: 'top',
//     constrain: true,
//     x: 700, y: 345,
//     width: 700,
//     height: 450,
//     // alwaysOnTop: 6,
//     stateful: true,
//     initComponent: function () {
//         this.stateId = this.stateId || Ext.getClass(this).getName();

        
//         // zIndexManager.register(compB);

//         this.callParent(arguments); 
//     },
//     items: [{
//         xtype: 'gridpanel',
        
//         store: {
//             type: "TraderFutureOrdersMibSummary",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//                 renderer: function (value, rec, rowrec) {
//                     if (rowrec.data.type == 'CompanySell'){
//                         rec.style = 'color:#209474'
//                     }
//                     if (rowrec.data.type == 'CompanyBuy'){
//                         rec.style = 'color:#d07b32'
//                     }
//                     return Ext.util.Format.htmlEncode(value)
//                 }, 
//             },
//             { text: 'Order Type', dataIndex: 'ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right' },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     },{
//         xtype: 'gridpanel',
//         store: {
//             type: "TraderFutureOrdersMib",
//             pageSize: 5,
//         },
//         columns: [
//             { text: 'Created Date', dataIndex: 'orq_createdon', filter: { type: 'string' }, },
//             { text: 'Order Price Target', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130, align: 'right' },
//             { text: 'Total Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
//             },
//             { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
//             },
//             { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
//         ],
//         bbar: {
//             xtype: 'pagingtoolbar',
//             displayInfo: true
//         }
//     }],
// },)