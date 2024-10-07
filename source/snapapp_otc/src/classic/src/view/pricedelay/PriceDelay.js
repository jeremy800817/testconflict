
Ext.define('snap.view.pricedelay.PriceDelay', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'pricedelayview',
    requires: [
        'snap.store.PriceDelay',
        'snap.model.PriceDelay',
        'snap.view.pricedelay.PriceDelayController',
        // 'snap.view.priceadjuster.PriceAdjusterModel',  
        // 'snap.store.ProductItems',
        // 'snap.model.ProductItems', 

    ],
    permissionRoot: '/root/system/priceadjuster',
    store: { type: 'PriceDelay' },
    controller: 'pricedelay-pricedelay',
    viewModel: {
        type: 'pricedelay-pricedelay'
    },
    enableFilter: true,
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
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Price Source', dataIndex: 'pricesource', filter: { type: 'string' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Delay ', dataIndex: 'delay', filter: { type: 'int' }, minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
      
        { text: 'Created By', dataIndex: 'createdby', filter: { type: 'string' }, minWidth: 100 },

    ],
    formClass: 'snap.view.pricedelay.PriceDelayGridForm'
});
