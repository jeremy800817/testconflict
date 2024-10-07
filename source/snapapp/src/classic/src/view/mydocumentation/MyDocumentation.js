Ext.define('snap.view.mydocumentation.MyDocumentation', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mydocumentationview',
    requires: [
        'snap.store.MyDocumentation',
        'snap.model.MyDocumentation',
        'snap.view.mydocumentation.MyDocumentationController',
        'snap.view.mydocumentation.MyDocumentationModel'
    ],
    permissionRoot: '/root/system/documentation',
    store: { type: 'MyDocumentation' },
    controller: 'mydocumentation-mydocumentation',
    viewModel: {
        type: 'mydocumentation-mydocumentation'
    },
    detailViewWindowHeight: 400,
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', 'add', 'edit', 'delete',
    ],
    columns: [
        {
            text: 'No', dataIndex: 'rowIndex',
            renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                return rowIndex + 1;
            }, flex: 1
        },
        { text: 'Code', dataIndex: 'code', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Locales', dataIndex: 'locales', filterable: false, minWidth: 130, flex: 1, sortable: false },
        { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, flex: 1 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, flex: 1 },
        { text: 'Created by', dataIndex: 'createdbyname', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: { type: 'string' }, minWidth: 100, flex: 1 }
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    formConfig: {
        controller: 'mydocumentation-mydocumentation',
        viewModel: {
            data: {
            }
        },

        formDialogWidth: 950,
        formDialogTitle: 'Documentation',
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
                reference: 'mydocumentationtab',
                items: [
                    {
                        title: 'Documentation Details',
                        layout: 'vbox',
                        margin: '28 8 8 18',
                        width: 550,
                        items: [
                            { xtype: 'hidden', hidden: true, name: 'id' },
                            {
                                xtype: 'combobox', fieldLabel: 'Code',
                                store: { data: [{ type: 'TNC' }, { type: 'PDPA' }, { type: 'DISCLAIMER' }, { type: 'FAQ' }, { type: 'PDS' }] },
                                queryMode: 'local', remoteFilter: false, name: 'code', valueField: 'type', displayField: 'type', reference: 'type', forceSelection: false, editable: false, allowBlank: false
                            },
                            { xtype: 'textfield', fieldLabel: 'Name', name: 'name', width: '90%' },
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
                            { xtype: 'displayfield', fieldLabel: 'Created On', name: 'createdon', width: '90%', format: 'Y-m-d H:i:s', editable: false, required: true, allowBlank: false },
                            { xtype: 'displayfield', fieldLabel: 'Created By', name: 'createdbyname', width: '90%' },
                            { xtype: 'displayfield', fieldLabel: 'Modified On', name: 'modifiedon', width: '90%', format: 'Y-m-d H:i:s', editable: false, required: true, allowBlank: false },
                            { xtype: 'displayfield', fieldLabel: 'Modified By', name: 'modifiedbyname', width: '90%' },
                        ]
                    },
                    {
                        title: 'Documentation Content',
                        layout: 'fit',
                        margin: '1',
                        items: [
                            {
                                xtype: 'textfield',
                                hidden: true,
                                name: 'myDocumentationTranslationParams',
                                reference: 'myDocumentationTranslationParams',
                                itemId: 'myDocumentationTranslationParams',
                                store: { type: 'array' },
                            },
                            {
                                reference: 'myDocumentationTranslation',
                                name: 'myDocumentationTranslation',
                                itemId: 'myDocumentationTranslation',
                                xtype: 'gridpanel',
                                title: '',
                                store: Ext.create('snap.store.MyDocumentationTranslation', {
                                    storeId: 'myDocumentationTranslationStore',
                                }),
                                tbar: [
                                    {
                                        itemId: 'addrec',
                                        text: 'Add',
                                        iconCls: 'fa fa-plus-circle',
                                        plain: true,
                                        handler: 'paramAddClick'
                                    },
                                    {
                                        itemId: 'removerec',
                                        text: 'Remove',
                                        iconCls: 'fa fa-minus-circle',
                                        plain: true,
                                        handler: 'paramDelClick',
                                        disabled: true
                                    }
                                ],
                                listeners: { viewReady: 'myDocumentationTranslationViewReady', selectionchange: 'paramsSelectionChange' },
                                sortableColumns: false,
                                columns: [
                                    {
                                        hidden: true, dataIndex: 'locfilecontent',
                                        editor: { xtype: 'textfield', name: 'locfilecontent' }
                                    },
                                    {
                                        text: 'Language', dataIndex: 'loclanguage', width: '15%',
                                        editor: {
                                            xtype: 'combobox', store: Ext.create('snap.store.MyLocale'), queryMode: 'local', remoteFilter: true,
                                            name: 'language', valueField: 'value', displayField: 'name', forceSelection: true, editable: false, allowBlank: false
                                        }, flex: 1
                                    },
                                    {
                                        text: 'File', dataIndex: 'locfilename', editor: {
                                            name: 'locfilename', xtype: 'filefield', listeners: {
                                                change: function (fld, value) {

                                                    var newValue = value.replace(/.+?fakepath\\(?=)/g, '');
                                                    fld.setRawValue(newValue);
                                                    fld.setValue(newValue);

                                                    fld.fileInputEl.dom.defaultValue = fld.rawValue;
                                                    fld.fileInputEl.dom.autocomplete = false;
                                                    var regex = new RegExp("(.*?)\.(pdf)");
                                                    var file = fld.getEl().down('input[type=file]').dom.files[0];
                                                    if (file) {
                                                        if (!(regex.test(fld.rawValue))) {
                                                            fld.setRawValue(null);
                                                            fld.setValue(null);

                                                            Ext.MessageBox.show({
                                                                title: 'Error Message',
                                                                msg: 'Please select a correct file format',
                                                                buttons: Ext.MessageBox.OK,
                                                                icon: Ext.MessageBox.ERROR
                                                            });

                                                            return;
                                                        }
                                                    
                                                        const file = fld.getEl().down('input[type=file]').dom.files[0];
                                                        const reader = new FileReader();
                                                        let locfilecontent = fld.prev().prev();
                                                        reader.onload = function (e) {
                                                            console.log(locfilecontent)
                                                            locfilecontent.setValue(e.target.result)
                                                        };
                                                    
                                                        reader.readAsDataURL(file);
                                                    }
                                                }
                                            }, allowBlank: false
                                        }, flex: 1
                                    },
                                    { text: 'Created By', dataIndex: 'loccreatedbyname', flex: 1 },
                                    { text: 'Created On', dataIndex: 'loccreatedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1 },
                                    { text: 'Modified By', dataIndex: 'locmodifiedbyname', flex: 1 },
                                    { text: 'Modified On', dataIndex: 'locmodifiedon',  xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: {type: 'date'}, flex: 1 },
                                ],
                                selType: 'rowmodel',
                                plugins: [
                                    {
                                        xclass: 'Ext.grid.plugin.RowEditing',
                                        clicksToEdit: 1,
                                        autoCancel: false,
                                        pluginId: 'editedRow1',
                                        id: 'editedRow1'
                                    }
                                ]

                            },
                        ]
                    }
                ]
            }
        ]
    },

});
