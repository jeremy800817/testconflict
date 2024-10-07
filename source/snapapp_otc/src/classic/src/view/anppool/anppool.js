Ext.define('snap.view.anppool.anppoollist',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'anpview',
    reference: 'anpgrid',
    requires: [
        'snap.store.AnpPool',
        'snap.model.AnpPool',
        'snap.view.anppool.anppoolController',
        // 'snap.view.anppool.anppoolModel',

    ],

    store: {
        type: 'AnpPool',
        autoLoad: true,
        // sorters: [{
        //     property: 'id',
        //     direction: 'desc'
        // }],
        // sorters: "id"
    },
    
    controller: 'anppool-anppool',
    // viewModel: {
    //     type: 'anppool-anppool'
    // },

    permissionRoot: '/root/mbb/anppool',
    enableFilter: true,
    // showToolbarItemIconOnly: false,

    toolbarItems: [
        'detail', 
        '|',
        'filter',
        '|',

        // filtering and printing export - start
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            text: 'Export', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Export Zip', tooltip: 'Export Zip To Email', iconCls: 'x-fa fa-envelope', handler: 'getPrintReportJob',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }
        // filtering and printing export - end
    ],

    columns: [
            {text: 'ID', dataIndex: 'id', hidden:true, filter: {type: 'string'}, flex: 1,},
            //{text: 'Type', dataIndex: 'type', filter: {type: 'string'}, flex: 1,},
            {text: 'Type',  dataIndex: 'operationtype', flex: 1,
                    filter: {
                        type: 'combo',
                        store: [
                            ['OrderConfirm', 'Order Confirm'],
                            ['OrderReverse', 'Order Reverse'],
                        ],

                    },
                    renderer: function(value, rec){
                        if(value=='OrderConfirm') return 'Order Confirm'
                        else if(value=='OrderReverse') return 'Order Reverse'
                        else return 'ERROR! No Data';
                    },
            },
            {text: 'Order No.', dataIndex: 'orderno', filter: {type: 'string'}, flex: 1, 
                renderer:function (value, rec, rowrec) {
                    if (rowrec.data.ordertype == 'CompanySell'){
                        rec.style = 'color:#209474'
                    }
                    if (rowrec.data.ordertype == 'CompanyBuy'){
                        rec.style = 'color:#d07b32'
                    }
                    return Ext.util.Format.htmlEncode(value)
                },
            },
            {text: 'Order Type', dataIndex: 'ordertype', filter: {type: 'string'}, flex: 1, },
            {text: 'Begin Price', dataIndex: 'beginprice', align: 'right', exportdecimal:2, filter: {type: 'string'}, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'End Price', dataIndex: 'endprice', align: 'right', exportdecimal:2, filter: {type: 'string'}, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'AP Price / g', dataIndex: 'amountppg', align: 'right', exportdecimal:2, filter: {type: 'string'}, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'Order XAU Amount', dataIndex: 'orderxau', align: 'right', exportdecimal:3, filter: {type: 'string'}, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000')},
            {text: 'AP Amount', dataIndex: 'amount', align: 'right', exportdecimal:2, filter: {type: 'string'}, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000')},
            
            {text: 'P1 Buy Price', dataIndex: 'p1buyprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P1 Sell Price', dataIndex: 'p1sellprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P1 Price On', dataIndex: 'p1priceon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, flex: 1},

            {text: 'P2 Buy Price', dataIndex: 'p2buyprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P2 Sell Price', dataIndex: 'p2sellprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P2 Price On', dataIndex: 'p2priceon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, flex: 1},

            {text: 'P3 Buy Price', dataIndex: 'p3buyprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P3 Sell Price', dataIndex: 'p3sellprice', align: 'right', exportdecimal:2, filter: { type: 'string' }, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00')},
            {text: 'P3 Price On', dataIndex: 'p3priceon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, flex: 1},

            {text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, flex: 1},
            {text: 'Order Created On', dataIndex: 'ordercreatedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, flex: 1},
            {text: 'Status',  dataIndex: 'status', flex: 1,
                    filter: {
                        type: 'combo',
                        store: [
                            ['0', 'Inactive'],
                            ['1', 'Active'],
                        ],

                    },
                    renderer: function(value, rec){
                        if(value=='1') return 'Active';
                        else return 'Inactive';
                    },
            },
    ],
    formConfig: {
        formDialogTitle: 'Mbb A and P Pool',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            labelWidth: 60,
            required: true
        },
        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
        ]
    },
});
