Ext.define('snap.view.trader.TraderOrders', {
    extend: 'Ext.panel.Panel',
    xtype: 'traderordersview',
    requires: [

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


    initialize: function () {
        _this = this;
        vm = this.getViewModel();

        this.callParent(arguments);
    },

    storeId: "TraderStoreAl",    
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    width: '100%',
    title:'Trader Order', 
    cls: Ext.baseCSSPrefix + 'shadow',

    items: {
        width: '100%',
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        style:{'margin-top':'5px'},
        scrollable: true,
        bodyPadding: 5,
        defaults: {
            frame: true,
            bodyPadding: 10
        },
        items: [
            {
                xtype: 'panel',
                title: 'Today Sales Total',
                bodyPadding: true,
                //collapsible: true,               
                closable: true,
                closeAction: 'hide',                               
                anchor: true,                
                width: '100%',
                
                items:[
                    {
                        xtype: 'grid',
                        store: {
                            type: "TraderOrders"
                        },
                        width:'100%',
                        minHeight:'120px',                          
                        detailViewWindowHeight: 400,  //Height of the view detail window
                        enableFilter: true,
                        layout: {
                            type: 'hbox',
                            align: 'fit',
                        },
                        columns: [
                            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' },minWidth:120 },
                            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' },minWidth:120 },
                            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' },minWidth:120},
                            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' },minWidth:120},
                            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' },minWidth:120},
                            { text: 'Avg GP', dataIndex: 'avg_gpprice', filter: { type: 'string' },minWidth:120 },
                            { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' },minWidth:120},
                        ]
                    },
                    {
                        xtype: 'menuseparator'
                    },
                    {
                        xtype: 'grid',
                        store: {
                            type: "TraderOrders1"
                        },
                        width:'100%',
                        minHeight:'120px',                          
                        detailViewWindowHeight: 400,  //Height of the view detail window
                        enableFilter: true,
                        layout: {
                            type: 'hbox',
                            align: 'fit',
                        },
                        columns: [
                            { text: 'Created Date', dataIndex: 'date', filter: { type: 'string' },minWidth:120 },
                            { text: 'Trx Count', dataIndex: 'total_trx', filter: { type: 'string' }, minWidth:120 },
                            { text: 'Amount Today', dataIndex: 'total_amount', filter: { type: 'string' }, minWidth:120 },
                            { text: 'Total Weight', dataIndex: 'total_xau', filter: { type: 'string' }, minWidth:120 },
                            { text: 'Avg FP', dataIndex: 'avg_gpprice', filter: { type: 'string' }, minWidth:120 },
                            { text: 'Order Type', dataIndex: 'ord_type', filter: { type: 'string' }, minWidth:120 },
                            { text: 'Product', dataIndex: 'pdt_name', filter: { type: 'string' }, minWidth:120 },
                        ]
                    }
                ]

            },
            {
                xtype: 'panel',
                title: 'Booking Orders',
                bodyPadding: true,
                //collapsible: true,               
                closable: true,
                closeAction: 'hide',                               
                anchor: true,                
                width: '100%',
                
                items:[
                    {
                        xtype: 'grid',
                        store: {
                            type: "TraderOrders4"
                        },
                        width:'100%',
                        minHeight:'120px',                          
                        detailViewWindowHeight: 400,  //Height of the view detail window
                        enableFilter: true,
                        layout: {
                            type: 'hbox',
                            align: 'fit',
                        },
                        columns: [
                            { text: 'GTP#', dataIndex: 'date', filter: { type: 'string' }, },
                            { text: 'Time', dataIndex: '', filter: { type: 'string' }, width: 120 },
                            { text: 'Product', dataIndex: '', filter: { type: 'string' }, width: 150 },
                            { text: 'Book by', dataIndex: '', filter: { type: 'string' }, width: 150 },
                            { text: 'Weight', dataIndex: '', filter: { type: 'string' }, width: 130 },
                            { text: 'GP Price', dataIndex: '', filter: { type: 'string' }, width: 130 },
                            { text: 'Refine Fee', dataIndex: '', filter: { type: 'string' }, width: 130 },
                            { text: 'Final', dataIndex: '', filter: { type: 'string' }, width: 130 },
                            { text: 'Value', dataIndex: '', filter: { type: 'string' }, width: 130 },
                            { text: 'Customer', dataIndex: '', filter: { type: 'string' }, width: 130 },
                        ]
                    },
                   
                ]

            },
            {
                xtype: 'panel',
                title: 'Customer Queue to Sell',
                bodyPadding: true,
                //collapsible: true,               
                closable: true,
                closeAction: 'hide',                               
                anchor: true,                
                width: '100%',
                
                items:[
                    {
                        xtype: 'grid',
                        store: {
                            type: "TraderOrders4"
                        },
                        width:'100%',
                        minHeight:'120px',                          
                        detailViewWindowHeight: 400,  //Height of the view detail window
                        enableFilter: true,
                        layout: {
                            type: 'hbox',
                            align: 'fit',
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
                        ]
                    },
                   
                ]

            },
            {
                xtype: 'panel',
                title: 'Customer Queue to Buy',
                bodyPadding: true,
                //collapsible: true,               
                closable: true,
                closeAction: 'hide',                               
                anchor: true,                
                width: '100%',
                
                items:[
                    {
                        xtype: 'grid',
                        store: {
                            type: "TraderOrders4"
                        },
                        width:'100%',
                        minHeight:'120px',                          
                        detailViewWindowHeight: 400,  //Height of the view detail window
                        enableFilter: true,
                        layout: {
                            type: 'hbox',
                            align: 'fit',
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
                        ]
                    },
                   
                ]

            }

        ]

    }
})