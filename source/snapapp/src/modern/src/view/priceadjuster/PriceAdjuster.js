Ext.define('snap.view.priceadjuster.PriceAdjuster', {
    extend: 'Ext.panel.Panel',
    xtype: 'priceadjusterview',
    title: 'Price Adjuster',
    requires: [
        'Ext.Button',
        'Ext.Img'
    ],
    controller: 'priceadjuster-priceadjuster',
    viewModel: {
        type: 'priceadjuster-priceadjuster'
    },
    layout: {
        align: 'stretch',
        type: 'hbox',
    },
    width: '100%',
    items: [{
        xtype: 'container',
        layout: {
            type: 'vbox',
            align: 'stretch',
        },
        width: '100%',
        items: [
            {
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
                            { xtype: 'button', reference: '', text: '', itemId: 'statusLgs', iconCls: 'x-fa fa-plus', handler: 'onAdd', validSelection: 'single' },
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
                            { xtype: 'button', reference: '', text: '', itemId: 'statusLgs', iconCls: 'x-fa fa-trash', handler: 'onDelete', validSelection: 'single', },
                            { xtype: 'label', html: 'Delete' }
                        ]
                    },
                ]
            },
            {
                xtype: 'grid',
                width: '100%',
                minHeight: '600px',
                permissionRoot: '/root/system/priceadjuster',
                store: { type: 'PriceAdjuster' },
                controller: 'priceadjuster-priceadjuster',
                viewModel: {
                    type: 'priceadjuster-priceadjuster'
                },
                gridShowDeleteSuccessfulMessage: true,
                id: 'priceadjustergrid',
                enableFilter: true,
                selectable: {
                    mode: 'multi',
                    checkbox: true
                },
                plugins: {
                    pagingtoolbar: true
                },
                listeners: {
                    painted: function () {
                        this.store.sort([{
                            property: 'id',
                            direction: 'DESC'
                        }]);
                        var columns = this.query('gridcolumn');
                        columns.find(obj => obj.text === 'ID').setVisible(false);
                    }
                },
                columns: [
                    { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, minWidth: 100 },
                    { text: 'Price Provider Name', dataIndex: 'priceprovidername', filter: { type: 'string' }, minWidth: 150 },
                    { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 160 },
                    {
                        text: 'Fx Buy Premium', dataIndex: 'fxbuypremium', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'fx sell premium', dataIndex: 'fxsellpremium', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Buy Margin', dataIndex: 'buymargin', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Sell Margin', dataIndex: 'sellmargin', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Refine Fee', dataIndex: 'refinefee', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Supplier Premium', dataIndex: 'supplierpremium', filter: { type: 'string' }, minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Buy Spread', dataIndex: 'buyspread', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    {
                        text: 'Sell Spread', dataIndex: 'sellspread', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                        editor: {
                            xtype: 'numberfield',
                            decimalPrecision: 3
                        }
                    },
                    { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 100 },

                ],

            }
        ]

    }]
});
Ext.define('snap.view.priceadjuster.FormModel', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'priceproviderid', type: 'string' },
        { name: 'fxbuypremium', type: 'string' },
        { name: 'fxsellpremium', type: 'string' },
        { name: 'buymargin', type: 'string' },
        { name: 'sellmargin', type: 'string' },
        { name: 'refinefee', type: 'string' },
        { name: 'supplierpremium', type: 'string' },
        { name: 'sellspread', type: 'string' },
        { name: 'buyspread', type: 'string' },
    ],

    validators: {
        priceproviderid: {
            type: 'presence', message: 'Select Price Provider'
        },
        fxbuypremium: {
            type: 'presence', message: 'invalid number'
        },
        fxsellpremium: {
            type: 'presence', message: 'invalid number'
        },
        buymargin: {
            type: 'presence', message: 'invalid number'
        },
        sellmargin: {
            type: 'presence', message: 'invalid number'
        },
        refinefee: {
            type: 'presence', message: 'invalid number'
        },
        supplierpremium: {
            type: 'presence', message: 'invalid number'
        },
        sellspread: {
            type: 'presence', message: 'invalid number'
        },
        buyspread: {
            type: 'presence', message: 'invalid number'
        },

    }
});