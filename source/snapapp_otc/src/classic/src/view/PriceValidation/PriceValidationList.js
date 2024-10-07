Ext.define('snap.view.PriceValidation.PriceValidationList', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'PriceValidationView',

    requires: [
        'snap.store.PriceValidation',
        'snap.model.PriceValidation',
        'snap.view.PriceValidation.pricevalidationController',
        'snap.view.PriceValidation.pricevalidationModel'
    ],

    controller: 'PriceValidation-PriceValidation',
    viewModel: {
        type: 'PriceValidation-PriceValidation',
    },
    store: { type: 'PriceValidation-PriceValidation', autoLoad: true },
    permissionRoot: '/root/system/pricevalidation',
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
    viewConfig: {
        getRowClass: function (record) {    
            record.data.price = parseFloat(record.data.price).toFixed(3);
        },            
    },
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
        { text: 'ID', dataIndex: 'id', hidden: true,  filter: { type: 'string' }, minWidth: 100 },
        { text: 'Partner Name', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 200 },
        { text: 'PartnerCode', dataIndex: 'partnercode', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Price Stream ID', dataIndex: 'pricestreamid', filter: { type: 'string' }, minWidth: 120 },
        { text: 'API Version', dataIndex: 'apiversion', filter: { type: 'string' }, minWidth: 100 },
        { text: 'UUID', dataIndex: 'uuid', filter: { type: 'string' }, minWidth: 150 },
        { text: 'RequestedType', dataIndex: 'requestedtype', filter: { type: 'string' }, minWidth: 130 },
        { text: 'PremiumFee', dataIndex: 'premiumfee', filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'RefineryFee', dataIndex: 'refineryfee', filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Price', dataIndex: 'price', filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }   },
        { text: 'Valid', dataIndex: 'validtill', filter: { type: 'string' },xtype: 'datecolumn',format: 'Y-m-d H:i:s', minWidth: 130 },
        { text: 'OrderID', dataIndex: 'orderid', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Reference', dataIndex: 'reference', filter: { type: 'string' }, minWidth: 130 },
        { text: 'TimeStamp', dataIndex: 'timestamp', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
        { text: 'CreatedOn', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, minWidth: 130 },
        { text: 'ModifiedOn', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Modified By', dataIndex: 'modifiedby', filter: { type: 'string' }, minWidth: 130 },
        //{text: 'Status', dataIndex: 'status'},

        //{text: 'Partner', dataIndex: 'partnername', filter: {type: 'string'}, flex: 1,},

        //{text: 'CreatedBy', dataIndex: 'createdbyname'},
        //{text: 'ModifiedBY', dataIndex: 'modifiedbyname'}

    ],
    formClass: 'snap.view.PriceValidation.pricevalidationGridForm'
    //     formConfig: {
    //     formDialogTitle: 'PriceValidation',
    //     enableFormDialogClosable: false,
    //     formPanelDefaults: {
    //         labelWidth: 60,
    //         required: true
    //     },
    //     formPanelItems: [
    //         { inputType: 'hidden', hidden: true, name: 'id' },
    //         { name: 'PremiumFee' },
    //     ]
    // }


});
