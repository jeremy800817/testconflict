Ext.define('snap.view.trader.TraderOrders', {
    extend: 'Ext.panel.Panel',
    xtype: 'traderordersview',
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
        this.stateId = this.stateId || Ext.getClass(this).getName();
        
        _this = this;
        vm = this.getViewModel();

        var timerCheckStore = function(store){
            if (store){
                store.load()
            }
        };

        // vm = this.up('traderordersview').viewModel;
        var countdown = 60;
        var timer = setInterval(function(){
            if (countdown <= 0){
                timerCheckStore(Ext.getStore('traderorders'))
                timerCheckStore(Ext.getStore('traderorders1'))
                timerCheckStore(Ext.getStore('traderorders1_2'))
                timerCheckStore(Ext.getStore('traderorders2'))
                timerCheckStore(Ext.getStore('traderorders3'))
                timerCheckStore(Ext.getStore('traderorders4'))
                timerCheckStore(Ext.getStore('traderorders5'))
                //timerCheckStore(Ext.getStore('traderorderspos01'))
                // timerCheckStore(Ext.getStore('traderorderspos02'))
                timerCheckStore(Ext.getStore('traderordersmib'))
                timerCheckStore(Ext.getStore('traderfutureordersmib'))
                timerCheckStore(Ext.getStore('traderfutureordersmibsummary'))
                timerCheckStore(Ext.getStore('traderordersgogoldsummary'))
                timerCheckStore(Ext.getStore('traderordersonecallsummary'))
                timerCheckStore(Ext.getStore('traderordersonecentsummary'))
                timerCheckStore(Ext.getStore('traderordersmcashsummary'))
                timerCheckStore(Ext.getStore('traderordersordertotals'))
                timerCheckStore(Ext.getStore('traderordersordertotals2'))
                timerCheckStore(Ext.getStore('traderordersbuysell'))
                timerCheckStore(Ext.getStore('traderorderscustomerqueuebuysell'))
                timerCheckStore(Ext.getStore('traderordersbuybacktotalpos'))
                timerCheckStore(Ext.getStore('traderordersbuybacktotalmiga'))
                countdown = 60;
            }
            vm.set('countdowntext', countdown);
            countdown -= 1;
        }, 1000)

        this.callParent(arguments);
    },
    
    storeId: "TraderStoreAl",

    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    cls: Ext.baseCSSPrefix + 'shadow',
    scrollable: true,

    items: {
        xtype: 'container',
        //layout: "fit",
        //height: 1000,
        //defaults: {
            //width: 400,
            //height: 250,
            // bodyPadding: 10,
            //autoShow: true,
            //cls: [],
        //},
        
        items: [
            {
                xtype: "panel",
                tbar: [
                    {
                        text: 'Clear Page State',
                        handler: function (btn) {
                            new Ext.Promise(function(fulfilled, rejected) {
                                Ext.MessageBox.confirm('Confirm', 'Are you sure you want to clear the page state?', function(btn) {
                                    if (btn == 'yes') fulfilled();
                                });
                            }).then(function(){
                                snap.getApplication().sendRequest({ 
                                    hdl: 'appstate', 
                                    action: 'deleteTraderOrder'
                                }).then(function(data, options) {
                                    if (data.success) {
                                        Ext.MessageBox.show({
                                            title: 'Info',
                                            msg: 'Page state clear successfully',
                                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.INFO,
                                            fn: function(btn) {
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        Ext.MessageBox.show({
                                            title: 'Error Message',
                                            msg: 'Failed to clear page state',
                                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                        });
                                    }
                                });
                            });
                        }
                    },
                    {
                        // xtype: 'button', // default for Toolbars
                        text: 'Refresh',
                        handler: function(){
                            var timerCheckStore = function(store){
                                if (store){
                                    store.load()
                                }
                            };
                            
                            timerCheckStore(Ext.getStore('traderorders'))
                            timerCheckStore(Ext.getStore('traderorders1'))
                            timerCheckStore(Ext.getStore('traderorders1_2'))
                            timerCheckStore(Ext.getStore('traderorders2'))
                            timerCheckStore(Ext.getStore('traderorders3'))
                            timerCheckStore(Ext.getStore('traderorders4'))
                            timerCheckStore(Ext.getStore('traderorders5'))
                            //timerCheckStore(Ext.getStore('traderorderspos01'))
                            // timerCheckStore(Ext.getStore('traderorderspos02'))
                            timerCheckStore(Ext.getStore('traderordersmib'))
                            timerCheckStore(Ext.getStore('traderfutureordersmib'))
                            timerCheckStore(Ext.getStore('traderfutureordersmibsummary'))
                            timerCheckStore(Ext.getStore('traderordersgogoldsummary'))
                            timerCheckStore(Ext.getStore('traderordersonecallsummary'))
                            timerCheckStore(Ext.getStore('traderordersonecentsummary'))
                            timerCheckStore(Ext.getStore('traderordersmcashsummary'))
                            timerCheckStore(Ext.getStore('traderordersordertotals'))
                            timerCheckStore(Ext.getStore('traderordersordertotals2'))
                            timerCheckStore(Ext.getStore('traderordersbuysell'))
                            timerCheckStore(Ext.getStore('traderorderscustomerqueuebuysell'))
                            timerCheckStore(Ext.getStore('traderordersbuybacktotalpos'))
                            timerCheckStore(Ext.getStore('traderordersbuybacktotalmiga'))
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
            /*{
                xtype: 'trader_orders_todaysalestotal'
            },
            {
                xtype: 'trader_orders_bookingorders',
                
            },
            {
                xtype: 'trader_orders_exportorders',
                
            },
            {
                xtype: 'trader_orders_customerqueuetosell',
                
            },
            {
                xtype: 'trader_orders_customerqueuetobuy',
                
            },

            {
                xtype: 'trader_orders_posbuybacktotal',
                
            },

            {
                xtype: 'trader_orders_mibordertotal',
                
            },

            {
                xtype: 'trader_orders_mibfutureordertotal',
                
            },

            {
                xtype: 'trader_orders_gogoldordertotal',
                
            },
            
            {
                xtype: 'trader_orders_onecallordertotal',
                
            },
            
            {
                xtype: 'trader_orders_onecentordertotal',
                
            },
            
            {
                xtype: 'trader_orders_mcashordertotal',
                
            },*/
            {
                xtype: 'trader_orders_dashboard'
            }
        ]
    }
})

var panelHeight = 300;
var defaultPanelHeader = {
    height: 30,
    padding: '0 10 0 10'
};
var defaultClosable = false;
var defaultMultiColumnSort = true;

//partner order total top - trader_orders_todaysalestotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_todaysalestotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    
    store: {
        type: "TraderOrders",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    enablePagination: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'ord_createdon', filter: { type: 'string' }, },
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 80, align: 'right' },
        { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130, align: 'right' },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        /*{ text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },*/
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ]    
});

//gtp sales total bottom - trader_orders_todaysalestotal2
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotal2', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_todaysalestotal2',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    
    store: {
        type: "TraderOrders1",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    enablePagination: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'ord_createdon', filter: { type: 'string' }, },
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right' },
        { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 110, align: 'right' },
        { text: 'Product', dataIndex: 'pdt_name', filter: { type: 'string' }, width: 130, align: 'right' },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ]    
});

//gtp sales total top - trader_orders_todaysalestotal1_2
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotal1_2', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_todaysalestotal1_2',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    
    store: {
        type: "TraderOrders1_2",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    enablePagination: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'ord_createdon', filter: { type: 'string' }, },
        //{ text: 'Partner', dataIndex: 'ord_partnerid', filter: { type: 'string' }, width: 130 },
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right' },
        { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 110, align: 'right' },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 110, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ]    
});

//gtp sales total panel
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotal_panel', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_todaysalestotal_panel',
    
    layout: {
        type: 'vbox',
        align: 'stretch',
        //padding: 5,
    },
    
    items: [{
        xtype: 'trader_orders_todaysalestotal1_2',
        height: '50%',
    }, {
        xtype: 'trader_orders_todaysalestotal2',
        flex: 1,
    }]
});

//trader_orders_bookingorders
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_bookingorders', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_bookingorders',
    
    store: {
        type: "TraderOrders2",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
    columns: [
        { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
        { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
        { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
        { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
        { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
        { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
        { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
        { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
        { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
        { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
    ]   
});

//trader_orders_exportorders
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_exportorders', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_exportorders',
    
    store: {
        type: "TraderOrders5",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
    columns: [
        { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
        { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
        { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
        { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
        { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
        { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
        { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
        { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
        { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
        { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
    ]  
});

//trader_orders_customerqueuetosell
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_customerqueuetosell', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_customerqueuetosell',
    
    store: {
        type: "TraderOrders3",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
    columns: [
        { text: 'GTP#', dataIndex: 'orq_orderqueueno', filter: { type: 'string' }, },
        { text: 'Time', dataIndex: 'orq_createdon', filter: { type: 'string' }, width: 120 },
        { text: 'Product', dataIndex: 'orq_productname', filter: { type: 'string' }, width: 150 },
        { text: 'Book by', dataIndex: 'orq_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
        { text: 'Weight', dataIndex: 'orq_xau', filter: { type: 'string' }, width: 130 },
        { text: 'Ask Price', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130 },
        { text: 'Refine Fee', dataIndex: 'orq_fee', filter: { type: 'string' }, width: 130 },
        { text: 'Final', dataIndex: 'orq_price', filter: { type: 'string' }, width: 130 },
        { text: 'Value', dataIndex: 'orq_amount', filter: { type: 'string' }, width: 130 },
        { text: 'Customer', dataIndex: 'orq_partnername', filter: { type: 'string' }, width: 130 },
    ] 
});

//trader_orders_customerqueuetobuy
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_customerqueuetobuy', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_customerqueuetobuy',
    
    store: {
        type: "TraderOrders4",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
    columns: [
        { text: 'GTP#', dataIndex: 'orq_orderqueueno', filter: { type: 'string' }, },
        { text: 'Time', dataIndex: 'orq_createdon', filter: { type: 'string' }, width: 120 },
        { text: 'Product', dataIndex: 'orq_productname', filter: { type: 'string' }, width: 150 },
        { text: 'Book by', dataIndex: 'orq_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
        { text: 'Weight', dataIndex: 'orq_xau', filter: { type: 'string' }, width: 130 },
        { text: 'Ask Price', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130 },
        { text: 'Refine Fee', dataIndex: 'orq_fee', filter: { type: 'string' }, width: 130 },
        { text: 'Final', dataIndex: 'orq_price', filter: { type: 'string' }, width: 130 },
        { text: 'Value', dataIndex: 'orq_amount', filter: { type: 'string' }, width: 130 },
        { text: 'Customer', dataIndex: 'orq_partnername', filter: { type: 'string' }, width: 130 },
    ] 
});

//POS Buyback Total - trader_orders_posbuybacktotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_posbuybacktotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_posbuybacktotal',
    
    store: {
        type: "TraderOrdersPOS01",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    
    columns: [
        { text: 'Created Date', dataIndex: 'byb_createdon', filter: { type: 'string' }, },
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right' },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, sortable: false },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        
    ]  
});

//Buyback Total Top - Pos
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_buybacktotal_pos', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_buybacktotal_pos',
    
    store: {
        type: "TraderOrdersBuybackTotalPos",
        pageSize: 5,
        filters: [{
            property: 'partnerid',
            value: '1',
            operator: '!='
        }],
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    
    columns: [
        { text: 'Created Date', dataIndex: 'byb_createdon', filter: { type: 'string' }, },
        { text: 'Partner', dataIndex: 'partnerid', filter: { type: 'number' }, width: 80},
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right' },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, sortable: false },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        
    ]  
});

//Buyback Total Top - Miga
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_buybacktotal_miga', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_buybacktotal_miga',
    
    store: {
        type: "TraderOrdersBuybackTotalMiga",
        pageSize: 5,
        filters: [{
            property: 'partnerid',
            value: '1',
            operator: '='
        }],
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    
    columns: [
        { text: 'Created Date', dataIndex: 'byb_createdon', filter: { type: 'string' }, },
        { text: 'Partner', dataIndex: 'partnerid', filter: { type: 'number' }, width: 80},
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right' },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, sortable: false },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        
    ]  
});

//Buyback Total Panel
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_buybacktotal_panel', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_buybacktotal_panel',
    
    layout: {
        type: 'vbox',
        align: 'stretch',
        //padding: 5,
    },
    
    items: [{
        xtype: 'trader_orders_buybacktotal_pos',
        height: '50%',
    }, {
        xtype: 'trader_orders_buybacktotal_miga',
        flex: 1,
    }]
});

//trader_orders_mibordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_mibordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_mibordertotal',
    
    store: {
        type: "TraderOrdersMib",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
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
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 3
            },
        },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
        { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ] 
});

//mib future order total top - trader_orders_mibfutureordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_mibfutureordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_mibfutureordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    
    store: {
        type: "TraderFutureOrdersMibSummary",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    enablePagination: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 100, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return value
            }, 
        },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
        },
        { text: 'Order Target Price', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        /*{ text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 90, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },*/
        { text: 'Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Order Type', dataIndex: 'ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
    ]
});

//mib future order total bottom - trader_orders_mibfutureordertotal2
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_mibfutureordertotal2', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_mibfutureordertotal2',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    
    store: {
        type: "TraderFutureOrdersMib",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'orq_createdon', filter: { type: 'string' }, },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 120, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')},
        { text: 'Total Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')},
        { text: 'Order Target Price', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 160, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
    ]
});

//mib future order total panel
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_mibfutureordertotal_panel', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_mibfutureordertotal_panel',
    
    layout: {
        type: 'vbox',
        align: 'stretch',
        //padding: 5,
    },
    
    items: [{
        xtype: 'trader_orders_mibfutureordertotal',
        height: '40%',
    }, {
        xtype: 'trader_orders_mibfutureordertotal2',
        flex: 1,
    }]
});

//trader_orders_gogoldordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_gogoldordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_gogoldordertotal',
    
    store: {
        type: "TraderOrdersGogoldSummary",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
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
    ]
});

//trader_orders_onecallordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_onecallordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_onecallordertotal',
    
    store: {
        type: "TraderOrdersOnecallSummary",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
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
    ]
});

//trader_orders_onecentordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_onecentordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_onecentordertotal',
    
    store: {
        type: "TraderOrdersOnecentSummary",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
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
    ]
});

//trader_orders_mcashordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_mcashordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_mcashordertotal',
    
    store: {
        type: "TraderOrdersMcashSummary",
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    
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
    ]
});

//partner order total middle - trader_orders_ordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_ordertotal', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_ordertotal',
    
    store: {
        type: "TraderOrdersOrderTotals",
        pageSize: 5,
        filters: [{
            property: 'type',
            value: 'CompanyBuy',
            operator: '='
        }],
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'ord_createdon', filter: { type: 'string' }, },
        { text: 'Partner', dataIndex: 'partnerid', filter: { type: 'number' }, width: 80},
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return value;
            }, 
        },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 100, align: 'right' },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 80, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
        },
        { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 85, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        /*{ text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 80, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },*/
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 80, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
        },
        { text: 'Total Fee', dataIndex: 'total_fee', filter: { type: 'string' }, width: 100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ]
});

//partner order total bottom - trader_orders_ordertotal
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_ordertotal2', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_ordertotal2',
    
    store: {
        type: "TraderOrdersOrderTotals2",
        filters: [{
            property: 'type',
            value: 'CompanySell',
            operator: '='
        }],
        pageSize: 5,
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    resizable: {
        split: true,
    },
    
    columns: [
        { text: 'Created Date', dataIndex: 'ord_createdon', filter: { type: 'string' }, },
        { text: 'Partner', dataIndex: 'partnerid', filter: { type: 'number' }, width: 80},
        { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 50, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return value;
            }, 
        },
        { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 100, align: 'right' },
        { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 80, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
        },
        { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 85, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        /*{ text: 'Avg FP', dataIndex: 'avg_fpprice', filter: { type: 'string' }, width: 80, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },*/
        { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 80, align: 'right',
            renderer: function (value, rec, rowrec) {
                if (rowrec.data.type == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.type == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.number(value, '0,000.000')
            }, 
        },
        { text: 'Total Fee', dataIndex: 'total_fee', filter: { type: 'string' }, width: 100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
    ]
});

//partner order total panel
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_ordertotal_panel', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_ordertotal_panel',
    
    layout: {
        type: 'vbox',
        align: 'stretch',
        //padding: 5,
    },
    
    items: [{
        xtype: 'trader_orders_todaysalestotal',
        height: '28%',
    }, {
        xtype: 'trader_orders_ordertotal',
        flex: 1,
    }, {
        xtype: 'trader_orders_ordertotal2',
        flex: 1,
    }]
});

//Company Buy & Sell - trader_orders_buysell
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_buysell', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_buysell',
    
    store: {
        type: "TraderOrdersBuySell",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    
    columns: [
        { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 100 },
        { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
        { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130 },
        { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 100 },
        { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 80, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'GP', dataIndex: 'ord_price', filter: { type: 'string' }, width: 80, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Refine/Premium Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 80, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'FP', dataIndex: 'ord_fpprice', filter: { type: 'string' }, width: 80, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 140, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Book by', dataIndex: 'ord_byweight', filter: { type: 'string' }, width: 150 },
        { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
    ]   
});

//Customer Queue Buy & Sell - trader_orders_customerqueuebuysell
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_customerqueuebuysell', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'trader_orders_customerqueuebuysell',
    
    store: {
        type: "TraderOrdersCustomerQueueBuySell",
    },
    controller: 'gridpanel-base',
    
    enableToolbar: false,
    enableContextMenu: false,
    multiColumnSort: defaultMultiColumnSort,
    
    columns: [
        
        { text: 'Time', dataIndex: 'orq_createdon', filter: { type: 'string' }, width: 120 },
        { text: 'Customer', dataIndex: 'orq_partnername', filter: { type: 'string' }, width: 130 },
        { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130 },
        { text: 'Product', dataIndex: 'orq_productname', filter: { type: 'string' }, width: 150 },
        { text: 'Weight', dataIndex: 'orq_xau', filter: { type: 'string' }, width: 90, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Ask Price', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 90, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Value', dataIndex: 'orq_amount', filter: { type: 'string' }, width: 90, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Book by', dataIndex: 'orq_byweight', filter: { type: 'string' }, width: 150 },
        { text: 'Refine Fee', dataIndex: 'orq_fee', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'Matched Price', dataIndex: 'orq_matchprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
        { text: 'GTP#', dataIndex: 'orq_orderqueueno', filter: { type: 'string' }, },
    ] 
});

//trader_orders_todaysalestotalchart1
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotalchart1', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_todaysalestotalchart1',
    
    items: [{
        xtype: 'panel',
        hidden: true,
        itemId: 'chart1panel',
        bodyPadding: 10,
        html: '<br/><br/><center>No Records</center><br/><br/><br/>'
    }, {
        xtype: 'cartesian',
        hidden: true,
        itemId: 'chart1',
        width: '100%',
        height: 280,
        insetPadding: '10 30 10 10',
        //hidden: true,
        animation: {
            easing: 'easeOut',
            duration: 500
        },
        store: {
            type: 'TraderOrdersChainedStore',
            listeners: {
                load: function(store, records, successful, operation, eOpts) {
                    if (0 == store.getCount()) {
                        Ext.ComponentQuery.query('#chart1panel')[0].setHidden(false);
                    } else {
                        Ext.ComponentQuery.query('#chart1')[0].setHidden(false);
                    }
                },
            }
        },
        axes: [{
            type: 'numeric',
            position: 'left',
            fields: 'total_amount',
            label: {
                renderer: Ext.util.Format.numberRenderer('0,0'),
                font: '10px sans-serif'
            },
            title: 'Amount',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }, {
            type: 'numeric',
            position: 'right',
            fields: 'total_trx',
            label: {
                font: '10px sans-serif',
                renderer: Ext.util.Format.numberRenderer('0,0')
            },
            title: 'Transactions',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }, {
            type: 'category',
            label: {
                font: '10px sans-serif',
                /*rotate: {
                    degrees: -45
                }*/
            },
            position: 'bottom',
            fields: 'ord_type_desc',
            title: 'Order Type',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }],
        series: [{
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'red'
            },
            xField: 'ord_type_desc',
            yField: ['total_amount', '',/*  '', '', '' */],
            label: {
                display: 'insideEnd',
                color: 'white',
                field: ['total_amount', '',/*  '', '', '' */],
                renderer: Ext.util.Format.numberRenderer('0,0.00'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Total amount : ' + record.get('total_amount'));
                }
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'orange'
            },
            xField: 'ord_type_desc',
            yField: ['', 'total_trx',/*  '', '', '' */],
            label: {
                display: 'insideEnd',
                color: 'white',
                field: ['', 'total_trx',/*  '', '', '' */],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml(record.get('total_trx') + ' Transactions');
                },
            }
        }, /* {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'yellow'
            },
            xField: 'ord_type_desc',
            yField: ['', '', 'total_xau', '', ''],
            label: {
                display: 'insideEnd',
                field: ['', '', 'total_xau', '', ''],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Total Weight : ' + record.get('total_xau'));
                },
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'green'
            },
            xField: 'ord_type_desc',
            yField: ['', '', '', 'avg_gpprice', ''],
            label: {
                display: 'insideEnd',
                field: ['', '', '', 'avg_gpprice', ''],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Avg FP : ' + record.get('avg_gpprice'));
                },
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'blue'
            },
            xField: 'ord_type_desc',
            yField: ['', '', '', '', 'avg_gpprice'],
            label: {
                display: 'insideEnd',
                field: ['', '', '', '', 'avg_gpprice'],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Avg GP : ' + record.get('avg_gpprice'));
                },
            }
        } */]
    }]
});

//trader_orders_todaysalestotalchart2
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard.trader_orders_todaysalestotalchart2', {
    extend: 'Ext.panel.Panel',
    xtype: 'trader_orders_todaysalestotalchart2',

    items: [{
        xtype: 'panel',
        hidden: true,
        itemId: 'chart2panel',
        bodyPadding: 10,
        html: '<br/><br/><center>No Records</center><br/><br/><br/>'
    }, {
        xtype: 'cartesian',
        hidden: true,
        itemId: 'chart2',
        width: '100%',
        height: 280,
        insetPadding: '10 30 10 10',
        animation: {
            easing: 'easeOut',
            duration: 500
        },
        store: {
            type: 'TraderOrders1ChainedStore',
            listeners: {
                load: function(store, records, successful, operation, eOpts) {
                    if (0 == store.getCount()) {
                        Ext.ComponentQuery.query('#chart2panel')[0].setHidden(false);
                    } else {
                        Ext.ComponentQuery.query('#chart2')[0].setHidden(false);
                    }
                },
            }
        },
        axes: [{
            type: 'numeric',
            position: 'left',
            fields: 'total_amount',
            label: {
                renderer: Ext.util.Format.numberRenderer('0,0'),
                font: '10px sans-serif'
            },
            title: 'Amount',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }, {
            type: 'numeric',
            position: 'right',
            fields: 'total_trx',
            label: {
                font: '10px sans-serif',
                renderer: Ext.util.Format.numberRenderer('0,0')
            },
            title: 'Transactions',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }, {
            type: 'category',
            label: {
                font: '10px sans-serif',
                /*rotate: {
                    degrees: -45
                }*/
            },
            position: 'bottom',
            fields: 'ord_type_desc',
            title: 'Order Type',
            labelTitle: {
                font: 'bold 11px sans-serif'
            },
        }],
        series: [{
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'red'
            },
            xField: 'ord_type_desc',
            yField: ['total_amount', '',/*  '', '', '' */],
            label: {
                display: 'insideEnd',
                color: 'white',
                field: ['total_amount', '',/*  '', '', '' */],
                renderer: Ext.util.Format.numberRenderer('0,0.00'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Total amount : ' + record.get('total_amount'));
                }
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'orange'
            },
            xField: 'ord_type_desc',
            yField: ['', 'total_trx', /* '', '', '' */],
            label: {
                display: 'insideEnd',
                color: 'white',
                field: ['', 'total_trx', /* '', '', '' */],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml(record.get('total_trx') + ' Transactions');
                },
            }
        }, /* {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'yellow'
            },
            xField: 'ord_type_desc',
            yField: ['', '', 'total_xau', '', ''],
            label: {
                display: 'insideEnd',
                field: ['', '', 'total_xau', '', ''],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Total Weight : ' + record.get('total_xau'));
                },
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'green'
            },
            xField: 'ord_type_desc',
            yField: ['', '', '', 'avg_gpprice', ''],
            label: {
                display: 'insideEnd',
                field: ['', '', '', 'avg_gpprice', ''],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Avg FP : ' + record.get('avg_gpprice'));
                },
            }
        }, {
            type: 'bar',
            stacked: false,
            style: {
                opacity: 0.80,
                color: 'blue'
            },
            xField: 'ord_type_desc',
            yField: ['', '', '', '', 'avg_gpprice'],
            label: {
                display: 'insideEnd',
                field: ['', '', '', '', 'avg_gpprice'],
                renderer: Ext.util.Format.numberRenderer('0,0'),
                orientation: 'vertical',
            },
            tooltip: {
                trackMouse: true,
                renderer: function(tooltip, record, item) {
                    tooltip.setHtml('Avg GP : ' + record.get('avg_gpprice'));
                },
            }
        } */]
    }]
});

//dashboard
Ext.define('snap.view.trader.TraderOrders.trader_orders_dashboard', {
    extend: 'Ext.dashboard.Dashboard',
    xtype: 'trader_orders_dashboard',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    maxColumns: 2,
    width: (1920 - 250),
    height: 800,
    scrollable: true,
    parts: {
        todaySalesTotal: {
            viewTemplate: {
                title: 'GTP Sales Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_todaysalestotal_panel'
                }]
            }
        },
        bookingOrders: {
            viewTemplate: {
                title: 'Booking Orders',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_bookingorders'
                }]
            }
        },
        exportOrders: {
            viewTemplate: {
                title: 'Export Orders',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_exportorders'
                }]
            }
        },
        customerQueueToSell: {
            viewTemplate: {
                title: 'Customer Queue to Sell',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_customerqueuetosell'
                }]
            }
        },
        customerQueueToBuy: {
            viewTemplate: {
                title: 'Customer Queue to Buy',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_customerqueuetobuy'
                }]
            }
        },
        posBuyBackTotal: {
            viewTemplate: {
                title: 'POS Buyback Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_posbuybacktotal'
                }]
            }
        },
        buyBackPanel: {
            viewTemplate: {
                title: 'Buyback Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_buybacktotal_panel'
                }]
            }
        },
        mibOrderTotal: {
            viewTemplate: {
                title: 'MIB Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_mibordertotal'
                }]
            }
        },
        mibFutureOrderTotal: {
            viewTemplate: {
                title: 'MIB Future Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_mibfutureordertotal_panel'
                }]
            }
        },
        goGoldOrderTotal: {
            viewTemplate: {
                title: 'GoGold Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_gogoldordertotal'
                }]
            }
        },
        oneCallOrderTotal: {
            viewTemplate: {
                title: 'OneCALL Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_onecallordertotal'
                }]
            }
        },
        oneCentOrderTotal: {
            viewTemplate: {
                title: 'OneCENT Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_onecentordertotal'
                }]
            }
        },
        mGoldOrderTotal: {
            viewTemplate: {
                title: 'MGold Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_mcashordertotal'
                }]
            }
        },
        orderTotal: {
            viewTemplate: {
                title: 'Partner Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_ordertotal'
                }]
            }
        },
        orderTotalPanel: {
            viewTemplate: {
                title: 'Partner Order Total',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_ordertotal_panel'
                }]
            }
        },
        companyBuySell: {
            viewTemplate: {
                title: 'Company Buy & Sell',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_buysell'
                }]
            }
        },
        customerQueueBuySell: {
            viewTemplate: {
                title: 'Customer Queue Buy & Sell',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_customerqueuebuysell'
                }]
            }
        },
        todaySalesTotalChart1: {
            viewTemplate: {
                title: 'Today Sales Total Chart(Partner)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_todaysalestotalchart1'
                }]
            }
        },
        todaySalesTotalChart2: {
            viewTemplate: {
                title: 'Today Sales Total Chart(GTP)',
                header: defaultPanelHeader,
                closable: defaultClosable,
                items: [{
                    xtype: 'trader_orders_todaysalestotalchart2'
                }]
            }
        }
    },
    defaultContent: [/*{
        type: 'todaySalesTotal',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'bookingOrders',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'exportOrders',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'customerQueueToSell',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'customerQueueToBuy',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'mibOrderTotal',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'goGoldOrderTotal',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'oneCallOrderTotal',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'oneCentOrderTotal',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'mGoldOrderTotal',
        columnIndex: 1,
        height: panelHeight,
    }, */{
        type: 'todaySalesTotal',
        columnIndex: 0,
        height: 452,
    }, {
        type: 'todaySalesTotalChart2',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'orderTotalPanel',
        columnIndex: 0,
        height: 718,
    }, {
        type: 'todaySalesTotalChart1',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'companyBuySell',
        columnIndex: 0,
        height: panelHeight,
    }, {
        type: 'customerQueueBuySell',
        columnIndex: 1,
        height: panelHeight,
    }, {
        type: 'buyBackPanel',
        columnIndex: 0,
        height: 452,
    }, {
        type: 'mibFutureOrderTotal',
        columnIndex: 1,
        height: panelHeight,
    }],
});

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

/* Ext.define('snap.view.trader.TraderOrders.trader_orders_todaysalestotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_todaysalestotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: false,
    title: 'Today Sales Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 45,
    width: 700,
    height: 400,
    items: [{
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders",
        },
        columns: [
            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120 },
            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130 },
        ]
    },{
        style: {"marginTop": '20px'},
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders1"
        },
        columns: [
            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120 },
            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, width: 130 },
            { text: 'Product', dataIndex: 'pdt_name', filter: { type: 'string' }, width: 130 },
        ],
    }],
})
Ext.define('snap.view.trader.TraderOrders.trader_orders_bookingorders',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_bookingorders',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: false,
    title: 'Booking Orders',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 445,
    width: 700,
    height: 350,
    items: [{
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders2"
        },
        columns: [
            { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
            { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
            { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
            { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
            { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
            { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
            { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
            { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
            { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
            { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }]
})
Ext.define('snap.view.trader.TraderOrders.trader_orders_exportorders',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_exportorders',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: false,
    title: 'Export Orders',
    headerPosition: 'top',
    constrain: true,
    x: 250, y: 445,
    width: 700,
    height: 350,
    items: [{
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders5"
        },
        columns: [
            { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
            { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
            { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
            { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
            { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
            { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
            { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
            { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
            { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
            { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }]
})
Ext.define('snap.view.trader.TraderOrders.trader_orders_customerqueuetosell',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_customerqueuetosell',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: false,
    title: 'Customer Queue to Sell',
    headerPosition: 'top',
    constrain: true,
    x: 700, y: 0,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders3",
        },
        columns: [
            { text: 'GTP#', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Time', dataIndex: '', filter: { type: 'string' }, width: 120 },
            { text: 'Product', dataIndex: '', filter: { type: 'string' }, width: 150 },
            { text: 'Book by', dataIndex: '', filter: { type: 'string' }, width: 150 },
            { text: 'Weight', dataIndex: '', filter: { type: 'string' }, width: 130 },
            { text: 'Ask Price', dataIndex: '', filter: { type: 'string' }, width: 130 },
            { text: 'Refine Fee', dataIndex: '', filter: { type: 'string' }, width: 130 },
            { text: 'Final', dataIndex: '', filter: { type: 'string' }, width: 130 },
            { text: 'Value', dataIndex: '', filter: { type: 'string' }, width: 130 },
            { text: 'Customer', dataIndex: '', filter: { type: 'string' }, width: 130 },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }]
})
Ext.define('snap.view.trader.TraderOrders.trader_orders_customerqueuetobuy',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_customerqueuetobuy',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: false,
    title: 'Customer Queue to Buy',
    headerPosition: 'top',
    constrain: true,
    x: 700, y: 200,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        store: {
            type: "TraderOrders4",
            // params: {
            //     start: 0,
            //     limit: itemsPerPage
            // }
        },
        columns: [
            { text: 'GTP#', dataIndex: 'ord_orderno', filter: { type: 'string' }, },
            { text: 'Time', dataIndex: 'ord_createdon', filter: { type: 'string' }, width: 120 },
            { text: 'Product', dataIndex: 'ord_productname', filter: { type: 'string' }, width: 150 },
            { text: 'Book by', dataIndex: 'ord_orderbyweightoramount', filter: { type: 'string' }, width: 150 },
            { text: 'Weight', dataIndex: 'ord_xau', filter: { type: 'string' }, width: 130 },
            { text: 'Ask Price', dataIndex: 'ord_bookingprice', filter: { type: 'string' }, width: 130 },
            { text: 'Refine Fee', dataIndex: 'ord_fee', filter: { type: 'string' }, width: 130 },
            { text: 'Final', dataIndex: 'ord_price', filter: { type: 'string' }, width: 130 },
            { text: 'Value', dataIndex: 'ord_amount', filter: { type: 'string' }, width: 130 },
            { text: 'Customer', dataIndex: 'ord_partnername', filter: { type: 'string' }, width: 130 },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }]
})        
Ext.define('snap.view.trader.TraderOrders.trader_orders_posbuybacktotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_posbuybacktotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'POS Buyback Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 45,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersPOS01",
            pageSize: 5,
        },
        columns: [
            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' }, },
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right' },
            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130 },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }],
})        
Ext.define('snap.view.trader.TraderOrders.trader_orders_mibordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_mibordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'MIB Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersMib",
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
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 3
                },
            },
            { text: 'Order Type', dataIndex: 'type', filter: { type: 'string' }, width: 130, align: 'right' },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }],
})        
Ext.define('snap.view.trader.TraderOrders.trader_orders_mibfutureordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_mibfutureordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'MIB Future Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 700, y: 345,
    width: 700,
    height: 450,
    // stateful: {
    //     width: true,
    //     height: true,
    // },
    stateful: true,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderFutureOrdersMibSummary",
            pageSize: 5,
        },
        columns: [
            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, width: 120, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.ordertype == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.ordertype == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.ordertype == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.ordertype == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 3
                }
            },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right',
                renderer: function (value, rec, rowrec) {
                    if (rowrec.data.ordertype == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.ordertype == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(parseFloat(value).toFixed(3))
                }, 
            },
            { text: 'Order Type', dataIndex: 'ordertype', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    },{
        xtype: 'gridpanel',
        store: {
            type: "TraderFutureOrdersMib",
            pageSize: 5,
        },
        columns: [
            { text: 'Created Date', dataIndex: 'orq_createdon', filter: { type: 'string' }, },
            { text: 'Order Price Target', dataIndex: 'orq_pricetarget', filter: { type: 'string' }, width: 130, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000') },
            { text: 'Total Amount', dataIndex: 'total_amount', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')
            },
            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, width: 150, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000')
            },
            { text: 'Order Type', dataIndex: 'orq_ordertype', filter: { type: 'string' }, width: 130, align: 'right' },
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true
        }
    }],
})        
Ext.define('snap.view.trader.TraderOrders.trader_orders_gogoldordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_gogoldordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'GoGold Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersGogoldSummary",
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

Ext.define('snap.view.trader.TraderOrders.trader_orders_onecallordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_onecallordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'OneCALL Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersOnecallSummary",
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
Ext.define('snap.view.trader.TraderOrders.trader_orders_onecentordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_onecentordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'OneCENT Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersOnecentSummary",
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
Ext.define('snap.view.trader.TraderOrders.trader_orders_mcashordertotal',{
    extend: 'Ext.window.Window',
    xtype: 'trader_orders_mcashordertotal',
    requires: [
        'snap.util.HttpStateProvider'
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        this.callParent(arguments); 
    },
    autoShow: true,
    title: 'MGold Order Total',
    headerPosition: 'top',
    constrain: true,
    x: 0, y: 345,
    width: 700,
    height: 300,
    items: [{
        xtype: 'gridpanel',
        
        store: {
            type: "TraderOrdersMcashSummary",
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
}) */
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