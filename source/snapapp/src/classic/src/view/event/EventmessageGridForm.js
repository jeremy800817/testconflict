Ext.define('snap.view.event.EventmessageGridForm',{
	extend: 'snap.view.gridpanel.GridForm',
	alias: 'widget.EventmessageGridForm',
	requires: [
		'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.store.Eventmessage',
        'snap.model.Eventmessage',
        'snap.view.event.EventmessageController',
        'snap.view.event.EventmessageModel'
    ],
    controller: 'eventmessage-eventmessage',
    viewModel: {
        data: {
            replacelist: [],
        }
    },
    reference:'formWindow',
    formDialogWidth: 950,
    formDialogHeight: 450,
    formDialogTitle: 'Event Message',
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
                { xtype: 'fieldset', title: 'Detail', collapsible: false,
                    default: { labelWidth: 90, layout: 'hbox'},
                    items: [
                        { xtype: 'hidden', hidden: true, name: 'id' },
                        { xtype: 'textfield', fieldLabel: 'Name', name: 'code', reference: 'code',  maxLength:255, required: true, allowBlank: false},
                        { xtype: 'textfield', fieldLabel: 'Subject', name: 'subject', reference: 'subject',  maxLength: 255, required: true, allowBlank: false},
                        { xtype: 'hidden', hidden: true, name: 'replace' },
                        { // button
                            buttonAlign: 'right',
                            buttons: [{
                                xtype: 'button',
                                formBind: true,
                                itemId: 'btnMapping',
                                text: 'refresh &raquo; tags',
                                handler: 'doMappingClicked',
                            },]
                        },
                    ]
                },
                { // richtext editor
                    xtype: 'htmleditor',
                    name: 'content',
                    reference: 'content',
                    // enableColors: true,
                    // enableAlignments: true,
                    // enableSourceEdit: true,
                    // enableFont: true,
                    // enableFontSize: true,
                    // enableFormat: true,
                    // enableLinks: true,
                    // enableLists: true,
                    height: 280,
                },
            ]
        },
        { xtype: 'panel', flex: 0, width: 10, height: 10, items: []}, //padding hbox
        {
            items: [
                { xtype: 'fieldset', title: 'Tags', collapsible: false,
                    default: { labelWidth: 90, layout: 'hbox'},
                    items: [
                        //Grid Panel Definition - grid data populated from onPreLoadForm() and replaceListViewReady()
                        { name: 'replacelist', xtype: 'gridpanel', reference: 'replaceListGrid', itemId: 'replaceListGrid', layout: 'fit',
                            flex: 1,
                            store: { type: 'array' , fields: ['name', 'value'] },
                            height: 420,
                            listeners: {
                                viewReady: 'replaceListViewReady',
                                selectionchange: 'replaceListSelectionChanged',
                                validateedit: 'onReplaceListValidate',
                                edit: 'onEditReplaceList'
                            },
                            columns: [
                                // { text: 'ID', dataIndex: 'id', inputType: 'hidden', hidden: true,
                                //     editor: { name: 'id', allowBlank: true }
                                // },
                                {reference: 'nameColumn', text: 'Name', dataIndex: 'name', 
                                    editor: {name: 'name',xtype: 'textfield', allowBlank: false} },
                                {reference: 'valueColumn', text: 'Value', dataIndex: 'value', 
                                    editor: {name: 'value',xtype: 'textfield', allowBlank: true}, flex: 1 },
                            ],
                            selType: 'rowmodel',
                            plugins: [
                                { ptype: 'rowediting', id: 'rowEditPlugin', clicksToMoveEditor: 1, autoCancel: false }
                            ]
                        }
                    ]
                },
            ]
        },
    ],
});
