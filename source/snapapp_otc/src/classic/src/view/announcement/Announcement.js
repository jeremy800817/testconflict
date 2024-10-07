Ext.define('snap.view.announcement.Announcement',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'announcementview',

    requires: [

        'snap.store.Announcement',
        'snap.model.Announcement',
        'snap.view.announcement.AnnouncementController',
        'snap.view.announcement.AnnouncementModel',
        

    ],
    formDialogWidth: 950,
    permissionRoot: '/root/system/announcement',
    store: { type: 'Announcement' },
    controller: 'announcement-announcement',
    viewModel: {
        type: 'announcement-announcement'
    },
    enableFilter: true,
    toolbarItems: [
        'add', 'edit', 'detail', '|', 'delete', 'filter','|',
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
        { text: 'Code',  dataIndex: 'code', filter: {type: 'string'}, flex: 1 },
        { text: 'Title', dataIndex: 'title', filter: {type: 'string'}, flex: 1 },
        { text: 'Description', dataIndex: 'description', filter: {type: 'string'},},
        
        { text: 'Content',  dataIndex: 'content', filter: {type: 'string'} , hidden: true, flex: 1 },
        //{ text: 'Content Repo',  dataIndex: 'contentrepo', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Rank',  dataIndex: 'content', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Type',  dataIndex: 'type', flex: 1 ,
                filter: {
                    type: 'combo',
                    store: [
                        ['PUSH', 'Push'],
                        ['ANNOUNCEMENT', 'Announcement'],
                    ],
                    renderer: function(value, rec){
                        if(value=='PUSH') return 'Push';
                        else return 'Announcement';
                    },
                },

        },
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
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

    formConfig: {
        controller: 'announcement-announcement',
        viewModel: {
            data: {
            }
        },

        formDialogWidth: 950,
        formDialogTitle: 'Announcement',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 0 0',
            scrollable: true,
            height: 530,
        },
        enableFormPanelFrame: true,
        formPanelLayout: 'hbox',
        formViewModel: {
        },
        formPanelItems: [
            {
                items:[{
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
                                { xtype: 'fieldset', title: 'Announcement Content', collapsible: false,
                                    default: { labelWidth: 90, layout: 'hbox'},
                                    items: [
                                        { xtype: 'hidden', hidden: true, name: 'id' },
                                        { xtype: 'textfield', fieldLabel: 'Code', name: 'code', width: '90%' },
                                        {
                                            itemId: 'user_partnerid',
                                            xtype: 'combobox',
                                            fieldLabel: 'Partner',
                                            allowBlank: true,
                                            name: 'partnerid',
                                            store: {
                                                autoLoad: true,
                                                type: 'Partner',
                                                sorters: 'name'
                                            },
                                            //disabled: 'true',
                                            listConfig: {
                                                getInnerTpl: function () {
                                                    return '[ {code} ] {name}';
                                                }
                                            },
                                            displayTpl: Ext.create('Ext.XTemplate',
                                                '<tpl for=".">',
                                                '[ {code} ] {name}',
                                                '</tpl>'
                                            ),
                                            displayField: 'name',
                                            valueField: 'id',
                                            typeAhead: true,
                                            queryMode: 'local',
                                            listeners: {
                                                expand: function(combo){
                                                    combo.store.load({
                                                        //page:2,
                                                        start: 0,
                                                        limit: 1500
                                                    })
                                                }
                                            },
                                            // forceSelection: true,
                                            // allowBlank: false
                                        },
                                        { xtype: 'textfield', fieldLabel: 'Title', name: 'title', width: '90%' },
                                        { xtype: 'textarea', fieldLabel: 'Description', name: 'description', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Content', name: 'content', width: '90%' },
                                        //{ xtype: 'textfield', fieldLabel: 'Content Repo', name: 'contentrepo', width: '90%' },
                                        { xtype: 'textfield', fieldLabel: 'Rank', name: 'rank', width: '90%' },
                                        //{ xtype: 'combobox', fieldLabel:'Announcement Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type', valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },
                                        { xtype: 'datefield', fieldLabel: 'Start On', name: 'displaystarton', width: '90%', format: 'Y-m-d H:i:s' },
                                        { xtype: 'datefield', fieldLabel: 'End On', name: 'displayendon', width: '90%', format: 'Y-m-d H:i:s' },
                                        //{ xtype: 'textfield', fieldLabel: 'Timer', name: 'timer', width: '90%' },
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
                                        },
                                        { xtype: 'radiogroup', fieldLabel: 'Is Mobile', width: '90%',
                                            items: [{
                                                boxLabel  : 'No',
                                                name      : 'ismobile',
                                                inputValue: '0'
                                            },{
                                                boxLabel  : 'Yes',
                                                name      : 'ismobile',
                                                inputValue: '1'
                                            },]
                                        }
                                    ]
                                }
                            ],
                        },
                        {
                            items: [
                                { xtype: 'fieldset', title: 'Picture', collapsible: false,
                                    default: { labelWidth: 90, layout: 'hbox'},
                                    items: [
                                        { xtype: 'filefield', name: 'picture', width: '90%' },
                                        { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                                    ]
                                },
                            ]
                        }
                    ]
                }]
            }
        ]
    },


});
