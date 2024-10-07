Ext.define('snap.view.priceproviders.PriceProvider', {
    extend: 'Ext.panel.Panel',
    xtype: 'priceproviderview',
    requires: [
        'snap.store.PriceProvider',
        'snap.model.PriceProvider',
        'snap.view.priceproviders.PriceProviderController',
        'snap.view.priceproviders.PriceProviderModel',
        'Ext.Button',
        'Ext.Img'
    ],
    title: 'Price Provider',
    layout: {
        align: 'stretch',
        type: 'hbox',
    },
    controller: 'priceprovider-priceprovider',
    viewModel: {
        type: 'priceprovider-priceprovider',
    },
    //store: { type: 'PriceProvider', autoLoad: true },
    permissionRoot: '/root/system/priceprovider',
    width: '100%',
    items: [{
        xtype: 'container',
        layout: {
            type: 'vbox',
            align: 'stretch',
        },
        width: '100%',
        items: [{
            xtype: 'toolbar',
            width: '100%',
            items: [
                {
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                    },
                    width: '20%',
                    style: 'text-align:center;font-size:0.75em',
                    items: [
                        { xtype: 'button', reference: '', text: '', itemId: 'statusLgs', iconCls: 'x-fa fa-plus', style: '', handler: 'addProvider', validSelection: 'single', },
                        { xtype: 'label', html: 'Add' }
                    ]
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                    },
                    width: '20%',
                    style: 'text-align:center;font-size:0.75em',
                    items: [
                        { xtype: 'button', reference: '', text: '', itemId: 'statusLgs', iconCls: 'x-fa fa-edit', style: '', handler: 'editProvider', validSelection: 'single', },
                        { xtype: 'label', html: 'Edit' }
                    ]
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                    },
                    width: '20%',
                    style: 'text-align:center;font-size:0.75em',
                    items: [
                        { xtype: 'button', reference: '', text: '', itemId: 'statusLgs', iconCls: 'x-fa fa-trash', style: '', handler: 'onDelete', validSelection: 'single', },
                        { xtype: 'label', html: 'Delete' }
                    ]
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                    },
                    width: '20%',
                    style: 'text-align:center;font-size:0.75em',
                    items: [
                        { xtype: 'button', reference: 'startButton', text: '', itemId: 'startPriceProvider', style: '', tooltip: 'Start Price Provider', iconCls: 'x-fa fa-play', handler: 'startPriceProvider', validSelection: 'single' },
                        { xtype: 'label', html: 'Start' }
                    ]
                },
                {
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                    },
                    width: '20%',
                    style: 'text-align:center;font-size:0.75em',
                    items: [
                        { xtype: 'button', reference: 'stopButton', text: '', itemId: 'stopPriceProvider', style: '', tooltip: 'Stop Price Provider', iconCls: 'x-fa fa-stop', handler: 'stopPriceProvider', validSelection: 'single' },
                        { xtype: 'label', html: 'Stop' }
                    ]
                },
            ]
        },
        {
            xtype: 'grid',
            width: '100%',
            layout: {
                type: 'hbox',
                align: 'fit',
            },
            selectable: {
                mode: 'single',
                checkbox: true
            },
            plugins: {
                pagingtoolbar: true
            },
            id: 'priceprovidergrid',
            permissionRoot: '/root/system/priceprovider',
            controller: 'priceprovider-priceprovider',
            viewModel: {
                type: 'priceprovider-priceprovider',
            },
            store: { type: 'PriceProvider', autoLoad: true },
            columns: [
                {
                    text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, minWidth: 80,
                },
                {
                    text: 'Code', dataIndex: 'code', filter: { type: 'string' }, minWidth: 130, cell: { encodeHtml: false }, renderer: function (val, record) {
                        if (record.data.isrunning == 0) {
                            //return 'Not Running';
                            return '<span style="margin-right:5px;color:#c23f10" class="fa fa-square"></span><span>' + val + '</span>';
                        } else if (record.data.isrunning == 1) {
                            //return 'Is Running';
                            return '<span style="margin-right:5px;color:#0aad3b" class="fa fa-square"></span><span>' + val + '</span>';
                        } else {
                            // Inactive    
                            return '<span style="margin-right:5px;color:#bdb9b7" class="fa fa-square"></span><span>' + val + '</span>';
                        }
                    }
                },
                //{text: 'Returnstatus', dataIndex: 'isrunning', filter: {type: 'string'}, flex: 1,},
                { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, flex: 1, minWidth: 180 },
                //{ text: 'Price Source', dataIndex: 'pricesourceid', filter: { type: 'int' }, hidden: true, minWidth: 130 },
                { text: 'Price Source Code', dataIndex: 'pricesourcecode', filter: { type: 'string' }, minWidth: 150 },
                { text: 'Product Category Name', dataIndex: 'productcategoryname', filter: { type: 'int' }, minWidth: 180 },
                { text: 'Pullmode', dataIndex: 'pullmode', filter: { type: 'int' }, minWidth: 100 },
                //{ text: 'Currency', dataIndex: 'currencyid', filter: { type: 'int' }, hidden: true, minWidth: 100 },
                { text: 'Currency', dataIndex: 'currencycode', filter: { type: 'string' }, minWidth: 100 },
                { text: 'Whitelist IP', dataIndex: 'whitelistip', filter: { type: 'string' }, minWidth: 150 },

                { text: 'URL', dataIndex: 'url', filter: { type: 'string' }, minWidth: 130 },
                { text: 'Connect Info', dataIndex: 'connectinfo', filter: { type: 'string' }, minWidth: 200 },
                { text: 'Lapse Time Allowance', dataIndex: 'lapsetimeallowance', filter: { type: 'int' }, minWidth: 160 },

                { text: 'Future Order Strategy', dataIndex: 'futureorderstrategy', filter: { type: 'string' }, minWidth: 160 },
                { text: 'Future Order Params', dataIndex: 'futureorderparams', filter: { type: 'string' }, minWidth: 150 },

                { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
                { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, flex: 1, hidden: true, minWidth: 100 },
                { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
                { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 100 },

                {
                    text: 'Status', dataIndex: 'status',
                    filter: {
                        type: 'combo',
                        store: [
                            ['0', 'Inactive'],
                            ['1', 'Active'],
                        ],

                    },
                    renderer: function (value, rec) {
                        if (value == '1') return 'Active';
                        else return 'Inactive';
                    },
                },

            ],
            height: '90%',
            width: '100%',
            listeners: {
                selectionchange: function (grid, re) {

                },

            },
        }
        ]

    }],
});

Ext.define('snap.view.priceprovider.FormModel', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'pricesourceid', type: 'string' },
        { name: 'whitelistip', type: 'string' },
        { name: 'url', type: 'string' },
        { name: 'connectinfo', type: 'string' },
        { name: 'code', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'productcategoryid', type: 'string' },
        { name: 'pullmode', type: 'string' },
        { name: 'currencyid', type: 'string' },
        { name: 'lapsetimeallowance', type: 'string' },
        { name: 'futureorderstrategy', type: 'string' },
        { name: 'futureorderparams', type: 'string' },
    ],
    validators: {
        pricesourceid: {
            type: 'presence', message: 'Invalid'
        },
        whitelistip: {
            type: 'presence', message: 'Invalid'
        },
        url: {
            type: 'presence', message: 'Invalid'
        },
        connectinfo: {
            type: 'presence', message: 'Invalid'
        },
        code: {
            type: 'presence', message: 'Invalid'
        },
        name: {
            type: 'presence', message: 'Invalid'
        },
        productcategoryid: {
            type: 'presence', message: 'Invalid'
        },
        pullmode: {
            type: 'presence', message: 'Invalid'
        },
        currencyid: {
            type: 'presence', message: 'Invalid'
        },
        lapsetimeallowance: {
            type: 'presence', message: 'Invalid'
        },
        futureorderstrategy: {
            type: 'presence', message: 'Invalid'
        },
        futureorderparams: {
            type: 'presence', message: 'Invalid'
        },

    }
});