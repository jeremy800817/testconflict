Ext.define('snap.view.mypaymentdetail.MyPaymentDetail',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mypaymentdetailview',

    requires: [
        'snap.store.MyPaymentDetail',
        'snap.model.MyPaymentDetail',
        'snap.view.mypaymentdetail.MyPaymentDetailController',
        'snap.view.mypaymentdetail.MyPaymentDetailModel'
    ],
    permissionRoot: '/root/bmmb/fpx',
    store: { type: 'MyPaymentDetail' },
    controller: 'mypaymentdetail-mypaymentdetail',

    viewModel: {
        type: 'mypaymentdetail-mypaymentdetail'
    },

    detailViewWindowHeight: 400,

    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        //'|',
        //{reference: 'cancelOrder', text: 'Cancel Order', itemId: 'cancelOrd', tooltip: 'Cancel Future Order', iconCls: 'x-fa fa-times', handler: 'cancelOrders', validSelection: 'single' }
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);           
        }
    },
    columns: [
        { text: 'ID',  dataIndex: 'id', filter: {type: 'string'}, hidden: true,minWidth:100, flex: 1 },
        { text: 'Amount',  dataIndex: 'amount', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Payment Ref No',  dataIndex: 'paymentrefno', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Gateway Ref No',  dataIndex: 'gatewayrefno', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Source Ref No',  dataIndex: 'sourcerefno', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Signed Data',  dataIndex: 'signeddata', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Location',  dataIndex: 'location', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Customer Fee',  dataIndex: 'customerfee', filter: {type: 'string'} , minWidth:130, flex: 1 },
        { text: 'Token',  dataIndex: 'token', filter: {type: 'string'} , minWidth:130, flex: 1 },
        { text: 'Status',  dataIndex: 'status', minWidth:100,

        filter: {
                    type: 'combo',
                    store: [
                        ['0', 'Pending'],
                        ['1', 'Processing'],
                        ['2', 'Success'],
                        ['3', 'Cancelled'],
                        ['4', 'Failed'],
                        ['5', 'Refunded'],

                    ],

                },
                renderer: function(value, rec){
                if(value=='0') return 'Pending';
                else if(value=='1') return 'Processing';
                else if(value=='2') return 'Success';
                else if(value=='3') return 'Cancelled';
                else if(value=='4') return 'Failed';
                else if(value=='5') return 'Refunded';
                else return 'Unidentified';
            },
        },
        
        { text: 'Transaction Date', dataIndex: 'transactiondate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
        { text: 'Requested on', dataIndex: 'requestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Success on', dataIndex: 'successon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
        { text: 'Failed on', dataIndex: 'failedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Refunded on', dataIndex: 'refundedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},

		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true,minWidth:100  },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, minWidth:100},
        
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

    formConfig: {
        controller: 'mypaymentdetail-mypaymentdetail',
        formDialogTitle: 'Payment Detail',

        // Form configuration

        formDialogWidth: 950,
            formDialogTitle: 'Payment Detail',
            enableFormDialogClosable: false,
            formPanelDefaults: {
                border: false,
                xtype: 'panel',
                flex: 1,
                layout: 'anchor',
                msgTarget: 'side',
               margins: '0 0 10 10'
            },
            enableFormPanelFrame: false,
            formPanelLayout: 'hbox',


        formPanelItems: [
        {  inputType: 'hidden', hidden: true, name: 'id' },
        {  xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },

        ]
    },

});
