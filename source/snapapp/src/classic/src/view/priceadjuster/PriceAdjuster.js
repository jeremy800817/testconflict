
Ext.define('snap.view.priceadjuster.PriceAdjuster', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'priceadjusterview',
    requires: [
        'snap.store.PriceAdjuster',
        'snap.model.PriceAdjuster',
        'snap.view.priceadjuster.PriceAdjusterController',
        // 'snap.view.priceadjuster.PriceAdjusterModel',  
        // 'snap.store.ProductItems',
        // 'snap.model.ProductItems', 

    ],
    permissionRoot: '/root/system/priceadjuster',
    store: { type: 'PriceAdjuster' },
    controller: 'priceadjuster-priceadjuster',
    viewModel: {
        type: 'priceadjuster-priceadjuster'
    },
    enableFilter: true,
    toolbarItems: [
        'add', 'detail', '|', 'filter',
        { 
            reference: 'quickAdjuster', text: 'Assign Ace Salesman', itemId: 'quickAdjuster', tooltip: 'Quick Price Adjuster', iconCls: 'x-fa fa-paper-plane', handler: 'quickAdjuster__',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Quick Price Adjuster'
                    });
                }
            }
        },
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },
    columns: [
        { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, minWidth: 100 },
        { text: 'Price Provider Name', dataIndex: 'priceprovidername', filter: { type: 'string' }, minWidth: 150 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Effective On', dataIndex: 'effectiveon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Effective End', dataIndex: 'effectiveendon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        {
            text: 'Tier', dataIndex: 'tier', filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', '1'],
                    ['1', '2'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return '2';
                else return '1';
            },
        },
        {
            text: 'Is Percentage', dataIndex: 'usepercent', filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },
        { text: 'Buy Percentage', dataIndex: 'buypercent', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.00'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 2
        }   },
        { text: 'Sell Percentage', dataIndex: 'sellpercent', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.00'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 2
        }   },
        { text: 'Fx Buy Premium', dataIndex: 'fxbuypremium', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Fx sell premium', dataIndex: 'fxsellpremium', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Buy Margin', dataIndex: 'buymargin', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Sell Margin', dataIndex: 'sellmargin', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Refine Fee', dataIndex: 'refinefee', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Supplier Premium', dataIndex: 'supplierpremium', filter: { type: 'string' }, minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Buy Spread', dataIndex: 'buyspread', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Sell Spread', dataIndex: 'sellspread', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),align: 'right',
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, minWidth: 100 },

    ],
    formClass: 'snap.view.priceadjuster.PriceAdjusterGridForm'
});
