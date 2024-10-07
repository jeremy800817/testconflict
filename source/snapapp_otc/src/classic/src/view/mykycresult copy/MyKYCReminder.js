Ext.define('snap.view.mykycreminder.MyKYCReminder',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mykycreminderview',

    requires: [
        'snap.store.MyKYCReminder',
        'snap.model.MyKYCReminder',
        'snap.view.mykycreminder.MyKYCReminderController',
        'snap.view.mykycreminder.MyKYCReminderModel'
    ],
    permissionRoot: '/root/bmmb/ekyc',
    store: { type: 'MyKYCReminder' },
    controller: 'mykycreminder-mykycreminder',

    viewModel: {
        type: 'mykycreminder-mykycreminder'
    },

    detailViewWindowHeight: 400,

    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', style : "width : 130px;",  text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
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
        { text: 'Index',  dataIndex: 'index', filter: {type: 'string'} , minWidth:70, maxWidth:70, flex: 1 },
        { text: 'Sent on', dataIndex: 'senton', minWidth: 370, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
        { text: 'Account Holder ID',  dataIndex: 'accountholderid', hidden: true, filter: {type: 'string'} , minWidth:130,  flex: 1 },
        // { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true,minWidth:100  },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, minWidth:100},
        // { text: 'Status',  dataIndex: 'status', minWidth:100,

        //        filter: {
        //            type: 'combo',
        //            store: [
        //                ['0', 'Inactive'],
        //                ['1', 'Active'],

        //            ],

        //        },
        //        renderer: function(value, rec){
        //           if(value=='0') return 'Inactive';
        //           else if(value=='1') return 'Active';
        //           else return 'Unidentified';
        //       },
        // }
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
        controller: 'mykycreminder-mykycreminder',
        formDialogTitle: 'EKYC Reminder',

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
