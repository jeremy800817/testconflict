Ext.define('snap.view.event.EventsubscriberGridForm',{
	extend: 'snap.view.gridpanel.GridForm',
	alias: 'widget.EventsubscriberGridForm',
	requires: [
		'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.store.Eventsubscriber',
        'snap.model.Eventsubscriber',
        'snap.view.event.EventsubscriberController',
        'snap.view.event.EventsubscriberModel'
    ],
    controller: 'eventsubscriber-eventsubscriber',
    viewModel: {
        data: {
            eventsubscriber: [],
        }
    },
    reference:'formWindow',
    formDialogTitle: 'Set Recipient(s)',
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
        {
            items: [
                // label
                { margin: '0 0 20 0', xtype: 'component', html: ['Please specify the email(s) for the multiple event(s) you have selected'] },
                { margin: '0 0 20 0', xtype: 'component', html: ['<b>List of Email Recipients (Seperate multiple emails with comma):</b>'] },

                // hidden field
                { xtype: 'hidden', hidden: true, name: 'objectid', bind: '{eventsubscriber.objectid}' },
                { xtype: 'hidden', hidden: true, name: 'triggerid', bind: '{eventsubscriber.triggerid}' },
                { xtype: 'hidden', hidden: true, name: 'groupid', bind: '{eventsubscriber.groupid}' },

                // receiver
                { xtype: 'textareafield', name: 'receiver', reference: 'receiver' }
            ]
        }
    ]
});
