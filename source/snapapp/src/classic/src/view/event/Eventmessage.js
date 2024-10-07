//
Ext.define('snap.view.event.Eventmessage',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'eventmessageview',

    requires: [
        'snap.store.Eventmessage',
        'snap.model.Eventmessage',
        'snap.view.event.EventmessageController',
        'snap.view.event.EventmessageModel'
    ],
    permissionRoot: '/root/system/event/event_message',
    store: { type: 'Eventmessage' },

    controller: 'eventmessage-eventmessage',

    viewModel: {
        type: 'eventmessage-eventmessage'
    },

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
    sortableColumns: false,
    columns: [
        { text: 'Name', dataIndex: 'code', filter: {type: 'string'  }, flex: 1 },
        { text: 'Subject', dataIndex: 'subject', filter: {type: 'string'  }, flex: 1 },
        { text: 'Body', dataIndex: 'shorten_content', filter: {type: 'string'  }, flex: 1 },
        { inputType: 'hidden', hidden: true, name: 'content' },
        { text: 'Status', dataIndex: 'status_text', filter: {type: 'string'  }, flex: 1 },
    ],

    // View properties settings
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 800,
    detailViewUseRawData: true,

    // Add/edit form settings
    formClass: 'snap.view.event.EventmessageGridForm',
});
