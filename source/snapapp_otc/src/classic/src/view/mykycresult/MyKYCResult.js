Ext.define('snap.view.mykycresult.MyKYCResult',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mykycresultview',

    requires: [
        'snap.store.MyKYCResult',
        'snap.model.MyKYCResult',
        'snap.view.mykycresult.MyKYCResultController',
        'snap.view.mykycresult.MyKYCResultModel'
    ],
    permissionRoot: '/root/bmmb/ekyc',
    store: { type: 'MyKYCResult' },
    controller: 'mykycresult-mykycresult',

    viewModel: {
        type: 'mykycresult-mykycresult'
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
        { text: 'ID',  dataIndex: 'id', filter: {type: 'string'}, hidden: true, minWidth:100, flex: 1 },
        { text: 'Provider',  dataIndex: 'provider', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Remarks',  dataIndex: 'remarks', filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Data',  dataIndex: 'data', hidden: true, filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Result',  dataIndex: 'result', hidden: true, filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Submission ID',  dataIndex: 'datsubmissionid', hidden: true, filter: {type: 'string'} , minWidth:130,  flex: 1 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true,minWidth:100  },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, minWidth:100},
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
        }
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
        controller: 'mykycresult-mykycresult',
        formDialogTitle: 'EKYC Result',

        // Form configuration

        formDialogWidth: 950,
            formDialogTitle: 'eKYC',
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
        ]
    },

});
