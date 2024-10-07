Ext.define('snap.view.mydisbursement.MyDisbursement',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mydisbursementview',

    requires: [
        'snap.store.MyDisbursement',
        'snap.model.MyDisbursement',
        'snap.view.mydisbursement.MyDisbursementController',
        'snap.view.mydisbursement.MyDisbursementModel'
    ],
    permissionRoot: '/root/bmmb/disbursement',
    store: { type: 'MyDisbursement' },
    controller: 'mydisbursement-mydisbursement',

    viewModel: {
        type: 'mydisbursement-mydisbursement'
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
        { text: 'Bank ID',  dataIndex: 'bankid', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Account Name',  dataIndex: 'accountname', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Account Number',  dataIndex: 'accountnumber', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Ace Bank Code',  dataIndex: 'acebankcode', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Fee',  dataIndex: 'fee', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Reference No',  dataIndex: 'refno', filter: {type: 'string'} , minWidth:130, flex: 1 },
        { text: 'Account Holder ID',  dataIndex: 'accountholderid', filter: {type: 'string'} , minWidth:130, flex: 1 },
        { text: 'Status',  dataIndex: 'status', minWidth:100,

        filter: {
                    type: 'combo',
                    store: [
                        ['0', 'Inactive'],
                        ['1', 'Active'],

                    ],

                },
                renderer: function(value, rec){
                if(value=='0') return 'Inactive';
                else if(value=='1') return 'Active';
                else return 'Unidentified';
            },
        },
        
        { text: 'Requested on', dataIndex: 'requestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
        { text: 'Disbursed on', dataIndex: 'disbursedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        
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
        controller: 'mydisbursement-mydisbursement',
        formDialogTitle: 'Disbursement',

        // Form configuration

        formDialogWidth: 950,
            formDialogTitle: 'Disbursement',
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
