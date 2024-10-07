Ext.define('snap.view.apilogs.apilogsList',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'apilogsview',

    requires: [
        'snap.store.ApiLogs',
        'snap.model.ApiLogs',
        'snap.view.apilogs.apilogsController',
        'snap.view.apilogs.apilogsModel',
    ],

    controller: 'apilogs',
    viewModel: {
        type: 'apilogs',
    },
    store: {type: 'ApiLogs', autoLoad: true},
    //store: [{ 'id' : 'adadada'}],
    permissionRoot: '/root/branch/apilog',
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
            {text: 'ID', dataIndex: 'id', hidden: true, filter: {type: 'string'}, flex: 1,},
            //{text: 'Type', dataIndex: 'type', filter: {type: 'string'}, flex: 1,},
            {text: 'Type',  dataIndex: 'type', flex: 2,
                    filter: {
                        type: 'combo',
                        store: [
                            ['gtp', 'gtp'],
                            ['sap', 'sap'],
                            ['NewPriceStream', 'NewPriceStream'],
                            ['NewPriceValidation', 'NewPriceValidation'],
                            ['SapOrder', 'SapOrder'],
                            ['SapCancelOrder', 'SapCancelOrder'],
                            ['SapGenerateGrn', 'SapGenerateGrn'],
                            ['SapGoldSerialRequest', 'SapGoldSerialRequest'],
                            ['ApiAllocateXau', 'ApiAllocateXau'],
                            ['ApiGetPrice', 'ApiGetPrice'],

                            ['ApiNewBooking', 'ApiNewBooking'],
                            ['ApiConfirmBooking', 'ApiConfirmBooking'],
                            ['ApiCancelBooking', 'ApiCancelBooking'],
                            ['ApiRedemption', 'ApiRedemption'],
                        ],

                    },
                    renderer: function(value, rec){
                        if(value=='gtp') return 'gtp';
                        else if(value=='sap') return 'sap';
                        else if(value=='NewPriceStream') return 'NewPriceStream';
                        else if(value=='NewPriceValidation') return 'NewPriceValidation';
                        else if(value=='SapOrder') return 'SapOrder';
                        else if(value=='SapCancelOrder') return 'SapCancelOrder';
                        else if(value=='SapGenerateGrn') return 'SapGenerateGrn';
                        else if(value=='SapGoldSerialRequest') return 'SapGoldSerialRequest';
                        else if(value=='ApiAllocateXau') return 'ApiAllocateXau';
                        else if(value=='ApiGetPrice') return 'ApiGetPrice';
                        else if(value=='ApiNewBooking') return 'ApiNewBooking';
                        else if(value=='ApiConfirmBooking') return 'ApiConfirmBooking';
                        else if(value=='ApiCancelBooking') return 'ApiCancelBooking';
                        else if(value=='ApiRedemption') return 'ApiRedemption';
                        else return 'ERROR! No Data';
                    },
            },
            {text: 'From IP', dataIndex: 'fromip', filter: {type: 'string'}, flex: 1,},
            //{text: 'System Initiate', dataIndex: 'systeminitiate', filter: {type: 'string'}, flex: 1,},
            {text: 'Request Data', dataIndex: 'requestdata', filter: {type: 'string'}, flex: 1,},
            {text: 'Response Data', dataIndex: 'responsedata', filter: {type: 'string'}, flex: 1,},
            {text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1},
            {text: 'Created By', dataIndex: 'createdbyname', hidden: true, filter: {type: 'string'}, flex: 1,},
            {text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1},
            {text: 'Modified By', dataIndex: 'modifiedbyname', hidden: true, filter: {type: 'string'}, flex: 1,},
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
    //formClass: 'snap.view.PriceValidation.pricevalidationGridForm'
        formConfig: {
        formDialogTitle: 'API logs',
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
