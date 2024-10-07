Ext.define('snap.view.pricestream.pricestreamList',{
    extend: 'snap.view.gridpanel.Base',
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
    store: {type: 'PriceStream', autoLoad: true},
    //store: [{ 'id' : 'adadada'}],
    permissionRoot: '/root/system/pricestream',
    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
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
            {text: 'ID', dataIndex: 'id', hidden: true,  filter: {type: 'string'}, flex: 1,},
            {text: 'Provider Name', dataIndex: 'providername', filter: {type: 'string'}, flex: 1,},
            {text: 'ProviderPrice ID', dataIndex: 'providerpriceid', filter: {type: 'string'}, flex: 1,},
            {text: 'UUID', dataIndex: 'uuid', filter: {type: 'string'}, flex: 1,},
            {text: 'Currency', dataIndex: 'categoryname', filter: {type: 'string'}, flex: 1,},
            {text: 'BUY per G', dataIndex: 'companybuyppg', filter: {type: 'string'}, flex: 1, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  },
            {text: 'SELL per G', dataIndex: 'companysellppg', filter: {type: 'string'}, flex: 1, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  },
            {text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1},
            {text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'}, flex: 1, hidden: true},
            {text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1},
            {text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'}, flex: 1, hidden: true},

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

            //{text: 'providername', dataIndex: 'providername', filter: {type: 'string'}, flex: 1,},
            //{text: 'createdbyname', dataIndex: 'createdbyname', filter: {type: 'string'}, flex: 1,},
            //{text: 'modifiedbyname', dataIndex: 'modifiedbyname', filter: {type: 'string'}, flex: 1,},

    ],
    //formClass: 'snap.view.PriceValidation.pricevalidationGridForm'
        formConfig: {
        formDialogTitle: 'Price Stream',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            labelWidth: 60,
            required: true
        },
        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
        ]
    }


});
