//
Ext.define('snap.view.event.Eventsubscriber',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'eventsubscriberview',

    requires: [
        'snap.store.Eventsubscriber',
        'snap.model.Eventsubscriber',
        'snap.view.event.EventsubscriberController',
        'snap.view.event.EventsubscriberModel'
    ],
    permissionRoot: '/root/system/event/event_subscription',
    store: { type: 'Eventsubscriber' },

    controller: 'eventsubscriber-eventsubscriber',

    viewModel: {
        type: 'eventsubscriber-eventsubscriber'
    },

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
    sortableColumns: false,

    toolbarDefaultEditItem: {reference: 'editButton', text: 'Set Recipient(s)', tooltip: 'Set Recipient(s)', iconCls:'x-fa fa-pencil', handler: 'onEdit', validSelection: 'single', enableMenu: true, permission: 'edit'},

    columns: [
        { text: 'Branch', dataIndex: 'branch_name', filter: {type: 'string'  }, flex: 2 },
        { text: 'Module', dataIndex: 'module_desc', filter: {type: 'string'  }, flex: 1 },
        { text: 'Action', dataIndex: 'action_desc', filter: {type: 'string'  }, flex: 1 },
        { text: 'Processor', dataIndex: 'processorclass', filter: {type: 'string'  }, flex: 1 },
        { text: 'Receiver', dataIndex: 'receiver', filter: {type: 'string'  }, flex: 3 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y/m/d H:i:s',  filter: {type: 'string'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s',  filter: {type: 'string'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Created by', dataIndex: 'createdby', filter: {type: 'int'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Modified by', dataIndex: 'modifiedby', filter: {type: 'int'  }, inputType: 'hidden', hidden: true, flex: 1 }
    ],

    features: [{
        id: 'group',
        ftype: 'grouping',
        groupHeaderTpl: '<b>{name}</b>',
        enableNoGroups: true,
    }],

    // View properties settings
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 400,
    detailViewUseRawData: true,

    // Add/edit form settings
    formClass: 'snap.view.event.EventsubscriberGridForm',
});
