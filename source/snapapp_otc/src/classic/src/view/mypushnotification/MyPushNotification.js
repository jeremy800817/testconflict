Ext.define('snap.view.mypushnotification.MyPushNotification',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mypushnotificationview',

    requires: [

        'snap.store.MyPushNotification',
        'snap.model.MyPushNotification',
        'snap.view.mypushnotification.MyPushNotificationController',
        'snap.view.mypushnotification.MyPushNotificationModel',
        

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/system/pushnotification',
    store: { type: 'MyPushNotification' },
    controller: 'mypushnotification-mypushnotification',
    viewModel: {
        type: 'mypushnotification-mypushnotification'
    },
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|', 'add', 'edit', 'delete', 
        //'detail', 'filter',
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
        { text: 'ID', dataIndex: 'id', hidden: true, filter: {type: 'int'}, flex: 1 },
        //{ text: 'Event Map ID',  dataIndex: 'eventmapid', hidden: true, filter: {type: 'string'}, flex: 1 },
        { text: 'Event Type',  dataIndex: 'eventtype', filter: {type: 'string'}, flex: 1, },
        { text: 'Code',  dataIndex: 'code', filter: {type: 'string'}, flex: 1 },
        { text: 'Icon', dataIndex: 'icon', filter: {type: 'string'}, flex: 1 },
        { text: 'Sound', dataIndex: 'sound', filter: {type: 'string'}, flex: 1 },
        { text: 'Rank',  dataIndex: 'rank', filter: {type: 'string'}, flex: 1 },
        { text: 'Valid From', dataIndex: 'validfrom',  xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Valid To', dataIndex: 'validto',  xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Status',  dataIndex: 'status',  flex: 2,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Inactive'],
                       ['1', 'Active'],
                       ['2', 'Queued'],
                   ],

               },
               renderer: function(value, rec){
                  if(value=='0') return 'Inactive';
                  if(value=='1') return 'Active';
                  else return 'Queued';
              },
        },
        { text: 'Created On', dataIndex: 'createdon',  xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Modified On', dataIndex: 'modifiedon',  xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'}, hidden: true },
       
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',


    formConfig: {
        controller: 'mypushnotification-mypushnotification',
        viewModel: {
            data: {
            }
        },

        formDialogWidth: 850,
        formDialogTitle: 'Push Notification',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 460,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {
                xtype: 'tabpanel',
                flex: 1,
                reference: 'mypushnotificationtab',
                items: [
                    {
                        title: 'Push Notification',
                        layout: {
                            type: 'table',
                            columns: 3,
                            tableAttrs: {
                                style: {
                                    width: '100%',
                                    height: '100%',
                                    top: '10px',
                                },
                            },
                            tdAttrs: {
                                valign: 'top',
                                height: '100%',
                                'background-color': 'grey',
                            }
                        },
                        xtype: 'container',
                        scrollable: false,
                        defaults: {
                            bodyPadding: '5',
                        },
                        items: [
                            {
                                items: [
                                    { xtype: 'fieldset', title: ' ', collapsible: false,
                                        default: { labelWidth: 90, layout: 'hbox'},
                                        items: [
                                            { xtype: 'hidden', hidden: true, name: 'id' },
                                            { xtype: 'hidden', hidden: true, name: 'contentparam' },
                                            { xtype: 'combobox', allowblank: false, fieldLabel:'Event', flex: 1, store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'eventtype', valueField: 'id', displayField: 'code', reference: 'event', forceSelection: false, editable: true, },
                                            { xtype: 'textfield', fieldLabel: 'Code', name: 'code', width: '90%' },
                                            { xtype: 'textfield', fieldLabel: 'Icon', name: 'icon', width: '90%' },
                                            { xtype: 'textfield', fieldLabel: 'Sound', name: 'sound', width: '90%' },
                                            { xtype: 'textfield', fieldLabel: 'Rank', name: 'rank', width: '90%' },
                                    
                                            { xtype: 'datefield', fieldLabel: 'Valid From', name: 'validfrom', width: '90%', format: 'Y-m-d H:i:s' },
                                            { xtype: 'datefield', fieldLabel: 'Valid To', name: 'validto', width: '90%', format: 'Y-m-d H:i:s' },
                                            
                                            { xtype: 'radiogroup', fieldLabel: 'Status', width: '90%',
                                                items: [{
                                                    boxLabel  : 'Inactive',
                                                    name      : 'status',
                                                    inputValue: '0'
                                                },{
                                                    boxLabel  : 'Active',
                                                    name      : 'status',
                                                    inputValue: '1'
                                                },]
                                            }
                                        ]
                                    }
                                ],
                            },
                        ],
                    },
                    {
                        title: 'Push Notification Content',
                        layout: 'fit',
                        margin: '1',
                        items: [
                            {
                                xtype: 'gridpanel',
                                reference: 'contentgrid',
                                title: '',
                                sortableColumns: false,
                                plugins: [
                                    { ptype: 'rowediting', clicksToEdit: 2, autoCancel: false , id: 'contentgrid-rowEditPlugin'}
                                ],
                                store: {
                                    fields: ['_id', 'language', 'title', 'body']
                                },
                                tbar: [
                                    { text: 'Add', iconCls: 'fa fa-plus-circle', handler: 'onContentAddPressed', reference: 'addBtn'},
                                    { text: 'Remove', iconCls: 'fa fa-plus-circle', handler: 'onContentRemovePressed', reference: 'removeBtn', disabled: true},
                                ],
                                listeners: {selectionchange: 'onContentSelectionChanged', viewReady: 'onContentViewReady' },
                                columns: [
                                    // { text: 'ID', dataIndex: '_id', hidden: true },
                                    {
                                        text: 'Language', dataIndex: 'language', flex: 1,
                                        editor: {
                                            xtype: 'combobox', store: {type: 'MyLocale'}, queryMode: 'local',
                                            valueField: 'value', displayField: 'name', name: 'language',
                                            editable: false, allowBlank: false, forceSelection: true
                                        }
                                    },
                                    { text: 'Title', dataIndex: 'title', editor: { name: 'title', xtype: 'textfield'}, allowBlank: false, flex: 2 },
                                    { text: 'Body', dataIndex: 'body', editor: { name: 'body', xtype: 'textarea'}, allowBlank: false, flex: 2 },
                                ],
                            }
                        ]
                    }
                ]
            },
        ]
    },


});
