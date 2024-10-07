Ext.define('snap.view.pricestream.pricestreamList',{
    extend: 'Ext.grid.Grid',
    xtype: 'pricestreamview',
    requires: [
        'snap.store.PriceStream',
        'snap.model.PriceStream',
        'snap.view.pricestream.pricestreamController',
        'snap.view.pricestream.pricestreamModel',
    ],
    controller: 'pricestream',
    viewModel: {
        type: 'pricestream',
    },
    title:'Price Stream',
    store: {type: 'PriceStream', autoLoad: true},
    permissionRoot: '/root/system/pricestream',
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
    plugins: {
        pagingtoolbar: true
    },
    columns: [
            {text: 'ID', dataIndex: 'id', hidden: true,  filter: {type: 'string'}, },
            {text: 'Provider Name', dataIndex: 'providername', filter: {type: 'string'},minWidth: 130, },
            {text: 'ProviderPrice ID', dataIndex: 'providerpriceid', filter: {type: 'string'},minWidth: 130, },
            {text: 'UUID', dataIndex: 'uuid', filter: {type: 'string'},minWidth: 130, },
            //{text: 'Currency ID', dataIndex: 'currencyid', filter: {type: 'string'}, flex: 1,},
            {text: 'Currency', dataIndex: 'categoryname', filter: {type: 'string'},minWidth: 130, },
            {text: 'BUY per G', dataIndex: 'companybuyppg', filter: {type: 'string'},  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  },
            {text: 'SELL per G', dataIndex: 'companysellppg', filter: {type: 'string'},  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  },
            //{text: 'Price Source ID', dataIndex: 'pricesourceid', filter: {type: 'string'}, flex: 1,},
            {text: 'Price Source', dataIndex: 'pricesourcename', filter: {type: 'string'}, minWidth: 130,},
            {text: 'Price Source Date', dataIndex: 'pricesourceon', xtype: 'datecolumn', format: 'Y-m-d H:i:s',filter: {type: 'date'},minWidth: 130, },
            {text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'},minWidth: 130, },
            {text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'},  hidden: true},
            {text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'},minWidth: 130, },
            {text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'},  hidden: true},
            //{text: 'Category Name', dataIndex: 'categoryname', filter: {type: 'string'}, flex: 1, hidden: true},

            {text: 'Status',  dataIndex: 'status',
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
    

});
