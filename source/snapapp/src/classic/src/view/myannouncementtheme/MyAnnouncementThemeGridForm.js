Ext.define('snap.view.myannouncementtheme.MyAnnouncementThemeGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.MyAnnouncementThemeGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.store.MyAnnouncementTheme',
        'snap.model.MyAnnouncementTheme',
        'snap.view.myannouncementtheme.MyAnnouncementThemeController',
        'snap.view.myannouncementtheme.MyAnnouncementThemeModel'
    ],
    controller: 'myannouncementtheme-myannouncementtheme',
    viewModel: {
        data: {
            replacelist: [],
        }
    },
    reference: 'formWindow',
    formDialogWidth: 950,
    formDialogHeight: 450,
    formDialogTitle: 'Announcement Theme',
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
            flex: 1,
            items: [
                {
                    xtype: 'fieldset', title: 'Detail', collapsible: false,
                    default: { labelWidth: 90, layout: 'hbox' },
                    items: [
                        { xtype: 'hidden', hidden: true, name: 'id' },
                        { xtype: 'textfield', fieldLabel: 'Name', name: 'name', reference: 'name', maxLength: 255, required: true, allowBlank: false },
                        { xtype: 'numberfield', fieldLabel: 'Rank', name: 'rank', reference: 'rank', required: true, allowBlank: false },
                        { xtype: 'datefield', fieldLabel: 'Start On', name: 'displaystarton', editable: false, required: true, allowBlank: false, format: 'Y-m-d H:i:s' },
                        { xtype: 'datefield', fieldLabel: 'End On', name: 'displayendon', editable: false, required: true, allowBlank: false, format: 'Y-m-d H:i:s' },
                        { xtype: 'datefield', fieldLabel: 'Valid From', name: 'validfrom', editable: false, required: true, allowBlank: false, format: 'Y-m-d H:i:s' },
                        { xtype: 'datefield', fieldLabel: 'Valid To', name: 'validto', editable: false, required: true, allowBlank: false, format: 'Y-m-d H:i:s' },
                        {
                            xtype: 'radiogroup', fieldLabel: 'Status', width: '90%',
                            items: [{
                                boxLabel: 'Inactive',
                                name: 'status',
                                inputValue: '0'
                            }, {
                                boxLabel: 'Active',
                                name: 'status',
                                inputValue: '1'
                            },]
                        }
                    ]
                },
            ]
        },
        { xtype: 'panel', flex: 0, width: 10, height: 10, items: [] }, //padding hbox
        {
            flex: 2,
            items: [
                {
                    xtype: 'fieldset', title: 'Template', collapsible: false,
                    default: { labelWidth: 90, layout: 'hbox' },
                    items: [
                        //Grid Panel Definition - grid data populated from onPreLoadForm() and replaceListViewReady()
                        { // richtext editor
                            xtype: 'textarea',
                            name: 'template',
                            reference: 'template',
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
            ]
        },
    ],
});
