Ext.define('snap.view.event.Eventlog',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'eventlogview',

    requires: [
        'snap.store.Eventlog',
        'snap.model.Eventlog',
        'snap.view.event.EventlogController',
        'snap.view.event.EventlogModel'
    ],
    permissionRoot: '/root/system/event/event_log',
    store: { type: 'Eventlog' },

    controller: 'eventlog-eventlog',

    viewModel: {
        type: 'eventlog-eventlog'
    },
    //title: 'Vendor',

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
    columns: [
        // { text: 'Trigger Id', dataIndex: 'triggerid', filter: {type: 'int'  } },
        // { text: 'Group Type ID', dataIndex: 'grouptypeid', filter: {type: 'int'  }, hidden: true },
        { text: 'Group Type', dataIndex: 'grouptypeid_text', filter: {type: 'string'  }, flex: 1 },
        // { text: 'Group ID', dataIndex: 'groupid', filter: {type: 'int'  }, hidden: true },
        { text: 'Branch Name', dataIndex: 'branchname', filter: {type: 'string'  }, flex: 2 },
        { text: 'Module', dataIndex: 'moduleid_text', filter: {type: 'string'  }, flex: 2 },
        { text: 'Action', dataIndex: 'actionid_text', filter: {type: 'string'  }, flex: 1 },
        // { text: 'Object Id', dataIndex: 'objectid', filter: {type: 'int'  } },
        { text: 'Object ID', dataIndex: 'objectid', filter: {type: 'string'  }, flex: 1 },
        { text: 'Reference', dataIndex: 'reference', filter: {type: 'string'  }, flex: 2 },
        { text: 'Subject', dataIndex: 'subject', filter: {type: 'string'  }, flex: 2 },
        { text: 'Send To', dataIndex: 'sendto', filter: {type: 'string'  }, flex: 2 },
        { text: 'Send On', dataIndex: 'sendon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'string'  }, flex: 1 },
        // { text: 'Log', dataIndex: 'log', filter: {type: 'string'  }, flex: 1 },
        {
            text: 'Log',
            align: 'center',
            stopSelection: true,
            xtype: 'actioncolumn',
            items: [{
                iconCls: 'x-fa fa-envelope',
                tooltip: 'View Log',
                handler: function(view, rowIndex, colIndex, item, e, record, row) {
                    Ext.create('Ext.window.Window', {
                        xtype: 'panel',
                        title: record.data.reference,
                        iconCls: 'x-fa fa-bell',
                        modal: true,
                        height: 500,
                        width: 550,
                        scrollable: 'vertical',
                        bodyPadding: 12,
                        html: record.data.log
                    }).show();
                }
            }],
            flex: 1
        }
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 650,
    detailViewWindowWidth: 560,
    detailViewSections: {default: 'Properties', log: 'Log'},
    detailViewUseRawData: true,
    detailViewConfig: {
        bodyCls: 'multiline_remarks',
        sourceConfig: {
            Details: {
            // Custom renderer to convert the html to text format
                renderer: function(value){
                    return value;
                }
            }
        }
    },

    toolbarItems: [
        'detail', 'filter',
    ]
});